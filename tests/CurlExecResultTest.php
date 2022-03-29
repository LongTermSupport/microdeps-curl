<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use MicroDeps\Curl\CurlExecResult;
use MicroDeps\Curl\CurlHandleFactory;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 * @covers \MicroDeps\Curl\CurlExecResult
 *
 * @small
 */
final class CurlExecResultTest extends TestCase
{
    private const SUCCESS_URL      = 'https://httpstat.us/200';
    private const UNSUCCESSFUL_URL = 'https://httpstat.us/500';
    private const ERROR_URL        = 'https://foo';

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
        self::assertSame('The requested URL returned error: 500 Internal Server Error', $exec->getError());
        self::assertStringContainsString(self::UNSUCCESSFUL_URL, $exec->getInfoAsString());
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
        $log = fopen('php://memory', 'ab+');
        if (false === $log) {
            throw new RuntimeException('Failed opening memory pointer');
        }
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToResource($log)
            ->createGetHandle(self::UNSUCCESSFUL_URL);
        CurlExecResult::exec($handle);
        rewind($log);
        $actual = stream_get_contents($log);
        fclose($log);
        if (!\is_string($actual)) {
            throw new RuntimeException('Failed getting string from log');
        }
        self::assertStringContainsString(self::UNSUCCESSFUL_URL, $actual);
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
        file_put_contents($filePath, '');
        $handle = (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToFile($filePath)
            ->createGetHandle(self::SUCCESS_URL);
        CurlExecResult::exec($handle);
        $actual = file_get_contents($filePath);
        if (!\is_string($actual)) {
            throw new RuntimeException('Failed getting contents from log file ' . $filePath);
        }
        self::assertStringContainsString('< HTTP/1.1 200 OK', $actual);
        $numLines = substr_count($actual, "\n");
        self::assertGreaterThan(20, $numLines);
    }
}
