<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use MicroDeps\Curl\Interface\CurlConfigAwareHandleInterface;
use MicroDeps\Curl\Interface\CurlHandleFactoryInterface;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
final class CurlHandleFactory extends AbstractCurlHandleFactory implements CurlHandleFactoryInterface
{
    /**
     * @param phpstanCurlOptions $options
     */
    public function createGetHandle(string $url, array $options = null): CurlConfigAwareHandleInterface
    {
        if (null !== $options) {
            $this->withOptions($options);
        }

        return new CurlConfigAwareHandle($url, $this->options);
    }

    public function createPostHandle(string $url, array $postData, array $options = []): CurlConfigAwareHandleInterface
    {
        $options[CURLOPT_POST]       = 1;
        $options[CURLOPT_POSTFIELDS] = $postData;
        $this->withOptions($options);

        return new CurlConfigAwareHandle($url, $this->options);
    }
}
