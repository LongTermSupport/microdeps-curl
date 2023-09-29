<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use MicroDeps\Curl\CurlException;
use MicroDeps\Curl\CurlHandleFactory;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \MicroDeps\Curl\CurlConfigAwareHandle
 * @covers \MicroDeps\Curl\CurlHandleFactory
 *
 * @small
 */
final class CurlHandleFactoryTest extends TestCase
{
    /**
     * @test
     *
     * @covers \MicroDeps\Curl\CurlException
     */
    public function itExceptsIfInvalidLogFile(): void
    {
        $this->expectException(CurlException::class);
        $this->expectExceptionMessage(substr(CurlException::MSG_DIRECTORY_NOT_CREATED, 0, 10));
        $filePath = '/invalid/path';
        (new CurlHandleFactory(new CurlOptionCollection()))->logToFile($filePath);
    }

    /** @test */
    public function itCanCreateALogFolderAsRequired(): void
    {
        $dirPath = __DIR__ . '/../var/' . __METHOD__;
        $logPath = "{$dirPath}/test.log";
        if (file_exists($logPath)) {
            \Safe\unlink($logPath);
        }
        if (is_dir($dirPath)) {
            \Safe\rmdir($dirPath);
        }
        (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToFile($logPath)
        ;
        self::assertDirectoryExists($dirPath);
        self::assertSame('750', decoct(\Safe\fileperms($dirPath) & 0777));
    }

    /** @test */
    public function itCanLogToResource(): void
    {
        $factory = (new CurlHandleFactory(new CurlOptionCollection()));
        $factory->logToResource(STDERR);
        $actual                        = $factory->createGetHandle('foo')->getOptions()->get();
        $expected                      = CurlOptionCollection::OPTIONS_DEFAULT;
        $expected[CURLOPT_VERBOSE]     = true;
        $expected[CURLINFO_HEADER_OUT] = false;
        $expected[CURLOPT_STDERR]      = STDERR;
        self::assertSame($expected, $actual);
    }

    /**
     * @test
     *
     * @covers \MicroDeps\Curl\CurlException
     */
    public function itExceptsOnInvalidLogFolder(): void
    {
        $this->expectException(CurlException::class);
        $this->expectExceptionMessage(
            sprintf(
                CurlException::MSG_DIRECTORY_NOT_CREATED,
                '/invalid/path',
                'mkdir(): Permission denied'
            )
        );
        $dirPath = '/invalid/path';
        $logPath = "{$dirPath}/test.log";
        (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToFile($logPath)
        ;
    }

    /** @test */
    public function itCanSetConfig(): void
    {
        $expected = 1;
        $factory  = new CurlHandleFactory();
        $factory->withOptions([CURLOPT_MAXREDIRS => $expected]);
        $client = $factory->createGetHandle('foo');
        $actual = $client->getOptions()->getOption(CURLOPT_MAXREDIRS);
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanUseDefinedConfig(): void
    {
        $expected = [CURLOPT_MAXREDIRS => 1];
        $factory  = new CurlHandleFactory(new CurlOptionCollection($expected));
        $client   = $factory->createGetHandle('foo');
        $actual   = $client->getOptions()->get();
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanGoInsecure(): void
    {
        $actual = (new CurlHandleFactory(new CurlOptionCollection()))
            ->insecure()
            ->createGetHandle('foo')->getOptions()->get()
        ;
        self::assertSame(0, $actual[CURLOPT_SSL_VERIFYPEER]);
        if (\defined('CURLOPT_SSL_VERIFYSTATUS')) {
            self::assertSame(0, $actual[CURLOPT_SSL_VERIFYSTATUS]);
        }
    }

    /** @test */
    public function itCanSetHeaders(): void
    {
        $expected = ['X-Foo: Bar'];
        $actual   = (new CurlHandleFactory(new CurlOptionCollection()))
            ->withHeaders($expected)
            ->createGetHandle('foo')->getOptions()->getOption(CURLOPT_HEADER)
        ;
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itCanAddOptionsWhenCreatingHandle(): void
    {
        $expected = 1;
        $actual   = (new CurlHandleFactory(new CurlOptionCollection()))
            ->createGetHandle('foo', [CURLOPT_MAXREDIRS => $expected])
            ->getOptions()->getOption(CURLOPT_MAXREDIRS)
        ;
        self::assertSame($expected, $actual);
    }
}
