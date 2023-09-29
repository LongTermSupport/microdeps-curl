<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Testing;

use MicroDeps\Curl\AbstractCurlHandleFactory;
use MicroDeps\Curl\CurlOptionCollection;
use MicroDeps\Curl\Interface\CurlConfigAwareHandleInterface;
use MicroDeps\Curl\Interface\CurlHandleFactoryInterface;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
final class MockHandleFactory extends AbstractCurlHandleFactory implements CurlHandleFactoryInterface
{
    private static MockHandle $mockHandleToReturn;

    public static function setMockHandleToReturn(MockHandle $mockHandle): void
    {
        self::$mockHandleToReturn = $mockHandle;
    }

    public function getOptions(): CurlOptionCollection
    {
        return $this->options;
    }

    /** @param phpstanCurlOptions $options */
    public function createGetHandle(string $url, array $options = null): CurlConfigAwareHandleInterface
    {
        if (null !== $options) {
            $this->withOptions($options);
        }

        return $this->createMock($url);
    }

    /**
     * @param array<string,string|int> $postData
     * @param phpstanCurlOptions       $options
     */
    public function createPostHandle(string $url, array $postData, array $options = []): CurlConfigAwareHandleInterface
    {
        $options[CURLOPT_POST]       = 1;
        $options[CURLOPT_POSTFIELDS] = $postData;
        $this->withOptions($options);

        return $this->createMock($url);
    }

    private function createMock(string $url): MockHandle
    {
        $mockHandle           = new MockHandle($url, $this->options);
        $mockHandle->response = self::$mockHandleToReturn->response;
        $mockHandle->success  = self::$mockHandleToReturn->success;
        $mockHandle->error    = self::$mockHandleToReturn->error;
        $mockHandle->info     = self::$mockHandleToReturn->info;

        return $mockHandle;
    }
}
