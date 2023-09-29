<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use MicroDeps\Curl\CurlExecResult;
use MicroDeps\Curl\CurlHandleFactory;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MicroDeps\Curl\CurlExecResult
 *
 * @small
 */
final class CurlExecResultTest extends TestCase
{
    private const SUCCESS_URL      = 'https://httpstat.us/200';
    private const UNSUCCESSFUL_URL = 'https://httpstat.us/500';
    private const ERROR_URL        = 'https://foo';

    private const POST_URL = 'http://ptsv3.com/t/asd/post/';

    /**
     * @test
     */
    public function itCanDoPostRequests(): void
    {
        $data   = ['foo' => 'bar'];
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))->createPostHandle(self::POST_URL, $data);
        $result = CurlExecResult::exec($handle);
        $info   = $result->getInfo();
        /* @phpstan-ignore-next-line offset does exist */
        self::assertSame('POST', $info['effective_method']);
        $response = $result->getResponse();
        self::assertStringContainsString('Thank you for this dump', $response);
    }

    /**
     * @test
     */
    public function itCanDoTheBasicExample(): void
    {
        // Build the Curl Handle
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))->createGetHandle('https://www.github.com');
        // Execute the handle and get a result object
        $result   = CurlExecResult::exec($handle);
        $response = $result->getResponse();
        self::assertNotEmpty($response);
    }

    /**
     * @test
     */
    public function itCanDoSuccessfulRequestsAndGetInfo(): void
    {
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))->createGetHandle(self::SUCCESS_URL);
        $result = CurlExecResult::exec($handle);
        self::assertTrue($result->isSuccess());
        self::assertSame('', $result->getError());
        self::assertStringContainsString(self::SUCCESS_URL, $result->getInfoAsString());
        $actual = $result->getInfo();
        $expect = [
            'url'          => 'https://httpstat.us/200',
            'content_type' => 'text/plain',
            'http_code'    => 200,
        ];
        foreach ($expect as $expectKey => $expectVal) {
            /* @phpstan-ignore-next-line offset does exist */
            self::assertSame($expectVal, $actual[$expectKey]);
        }
    }

    /**
     * @test
     */
    public function itCanDoUnsuccessfulRequests(): void
    {
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))->createGetHandle(self::UNSUCCESSFUL_URL);
        $exec   = CurlExecResult::try($handle);
        self::assertFalse($exec->isSuccess());
        self::assertStringContainsString(self::UNSUCCESSFUL_URL, $exec->getInfoAsString());
        self::assertSame(500, $exec->getInfo()['http_code'] ?? '');
    }

    /**
     * @test
     */
    public function itCanDoErrorRequests(): void
    {
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))->createGetHandle(self::ERROR_URL);
        $exec   = CurlExecResult::try($handle);
        self::assertFalse($exec->isSuccess());
        self::assertSame('Could not resolve host: foo', $exec->getError());
        self::assertStringContainsString(self::ERROR_URL, $exec->getInfoAsString());
    }

    /**
     * @test
     */
    public function itCanLog(): void
    {
        $log    = \Safe\fopen('php://memory', 'ab+');
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToResource($log)
            ->createGetHandle(self::ERROR_URL)
        ;
        CurlExecResult::try($handle);
        \Safe\rewind($log);
        $actual = \Safe\stream_get_contents($log);
        \Safe\fclose($log);
        self::assertStringContainsString(self::ERROR_URL, $actual);
        self::assertStringContainsString('Curl Error', $actual);
        $numLines = substr_count($actual, "\n");
        self::assertGreaterThan(20, $numLines);
    }

    /**
     * @test
     */
    public function itCanLogToFile(): void
    {
        $filePath = __DIR__ . '/../var/' . __METHOD__ . '.log';
        \Safe\file_put_contents($filePath, '');
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToFile($filePath)
            ->createGetHandle(self::SUCCESS_URL)
        ;
        CurlExecResult::exec($handle);
        $actual = \Safe\file_get_contents($filePath);
        self::assertStringContainsString('< HTTP/1.1 200 OK', $actual);
        $numLines = substr_count($actual, "\n");
        self::assertGreaterThan(20, $numLines);
    }
}
