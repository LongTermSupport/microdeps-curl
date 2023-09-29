<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Tests\Testing;

use MicroDeps\Curl\CurlExecResult;
use MicroDeps\Curl\Testing\MockHandle;
use MicroDeps\Curl\Testing\MockHandleFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 *
 * @small
 */
final class MockHandleFactoryTest extends TestCase
{
    public function testCreatePostHandle(): void
    {
        $factory              = new MockHandleFactory();
        $mockHandle           = new MockHandle('https://example.com', $factory->getOptions());
        $mockHandle->response = 'foo';
        $mockHandle->success  = true;
        $factory::setMockHandleToReturn($mockHandle);
        $handle = $factory->createGetHandle('https://example.com');
        $result = CurlExecResult::exec($handle);
        self::assertSame('foo', $result->getResponse());
        self::assertSame(true, $result->isSuccess());
    }
}
