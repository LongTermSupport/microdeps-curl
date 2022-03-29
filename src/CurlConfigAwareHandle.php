<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use CurlHandle;
use RuntimeException;
use ValueError;

/**
 * This is a very simple wrapper that allows us to keep track of config for a specific CurlHandle.
 */
final class CurlConfigAwareHandle
{
    private CurlHandle $handle;

    /**
     * @throws CurlException
     */
    public function __construct(public readonly string $url, private CurlOptionCollection $options)
    {
        $handle = curl_init($this->url);
        if (false === ($handle instanceof CurlHandle)) {
            throw new RuntimeException('Failed creating curl handle for url ' . $url);
        }
        $this->handle = $handle;
        $this->applyOptions();
    }

    public function getHandle(): CurlHandle
    {
        return $this->handle;
    }

    public function getOptions(): CurlOptionCollection
    {
        return $this->options;
    }

    /**
     * @throws CurlException
     */
    private function applyOptions(): void
    {
        try {
            curl_setopt_array($this->handle, $this->options->get());
        } catch (ValueError $valueError) {
            $valid   = array_flip(get_defined_constants(true)['curl']);
            $invalid = array_diff_key($this->options->get(), $valid);
            throw CurlException::withFormatAndPrevious(
                CurlException::MSG_INVALID_OPTIONS,
                $valueError,
                print_r($invalid, true),
                /* @phpstan-ignore-next-line confused by curl_version return type */
                curl_version()['version']
            );
        }
    }
}
