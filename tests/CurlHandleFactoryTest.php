<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests;

use MicroDeps\Curl\CurlException;
use MicroDeps\Curl\CurlHandleFactory;
use MicroDeps\Curl\CurlOptionCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 *
 * @small
 */
final class CurlHandleFactoryTest extends TestCase
{
    /** @test */
    public function itExceptsIfInvalidLogFile(): void
    {
        $this->expectException(CurlException::class);
        $this->expectExceptionMessage(substr(CurlException::MSG_DIRECTORY_NOT_CREATED, 0, 10));
        $filePath = '/invalid/path';
        (new CurlHandleFactory(new CurlOptionCollection()))
            ->logToFile($filePath)
        ;
    }
}
