<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use MicroDeps\Curl\CurlException;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class CurlOptionCollectionTest extends TestCase
{
    /** @test */
    public function itCanProvideValidOptions(): void
    {
        $expectedCount = 222;
        $actual        = CurlOptionCollection::validOptions();
        self::assertCount($expectedCount, $actual);
        $expected = [58 => 'CURLOPT_AUTOREFERER'];
        $actual   = [key($actual) => current($actual)];
        self::assertSame($expected, $actual);
    }

    /** @test */
    public function itExceptsOnInvalidOptions(): void
    {
        $invalid = [12345 => true];
        $this->expectException(CurlException::class);
        $this->expectExceptionMessage(
            sprintf(
                CurlException::MSG_INVALID_OPTIONS,
                print_r($invalid, true),
                /* @phpstan-ignore-next-line confused by curl_version return type */
                curl_version()['version']
            )
        );
        new CurlOptionCollection($invalid);
    }

    /** @test */
    public function itCanGetDebugOptions(): void
    {
        $expected = [
            'CURLOPT_FOLLOWLOCATION'  => true,
            'CURLOPT_RETURNTRANSFER'  => true,
            'CURLOPT_ACCEPT_ENCODING' => '',
            'CURLINFO_HEADER_OUT'     => true,
            'CURLOPT_FAILONERROR'     => true,
        ];
        $actual   = (new CurlOptionCollection())->getOptionsDebug();
        self::assertSame($expected, $actual);
    }
}
