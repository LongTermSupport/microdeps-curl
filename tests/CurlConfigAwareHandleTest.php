<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use CurlHandle;
use MicroDeps\Curl\CurlConfigAwareHandle;
use MicroDeps\Curl\CurlException;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MicroDeps\Curl\CurlConfigAwareHandle
 *
 * @internal
 *
 * @small
 */
final class CurlConfigAwareHandleTest extends TestCase
{
    /**
     * @test
     * @covers \MicroDeps\Curl\CurlException
     */
    public function itHandlesInvalidOptions(): void
    {
        $invalidOptions = [9999 => 1];
        $options        = CurlOptionCollection::OPTIONS_DEFAULT + $invalidOptions;
        $this->expectException(CurlException::class);
        $this->expectExceptionMessage(
            sprintf(
                CurlException::MSG_INVALID_OPTIONS,
                print_r($invalidOptions, true),
                    /* @phpstan-ignore-next-line confused by curl_version return type */
                    curl_version()['version']
            )
        );
        new CurlConfigAwareHandle('foo', new CurlOptionCollection($options));
    }

    /** @test */
    public function itCanGetRawHandle(): void
    {
        $handle = new CurlConfigAwareHandle('foo', new CurlOptionCollection([]));
        $actual = $handle->getHandle();
        self::assertInstanceOf(CurlHandle::class, $actual);
    }

    /** @test */
    public function itCanGetOptions(): void
    {
        $handle   = new CurlConfigAwareHandle('foo', new CurlOptionCollection());
        $expected = CurlOptionCollection::OPTIONS_DEFAULT;
        $actual   = $handle->getOptions()->get();
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itExceptsWhenFailingToCurlInit(): void
    {
        self::markTestSkipped('unable to find a way to make curl init fail');
//        $this->expectException(\RuntimeException::class);
//        new CurlConfigAwareHandle(str_repeat('a', 9999), new CurlOptionCollection());
    }
}
