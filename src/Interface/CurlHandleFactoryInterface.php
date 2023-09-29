<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Interface;

use MicroDeps\Curl\CurlException;
use MicroDeps\Curl\CurlOptionCollection;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
interface CurlHandleFactoryInterface
{
    public function insecure(): self;

    /** @param phpstanCurlOptions $options */
    public function withOptions(array $options): self;

    /**
     * @param string[] $headers
     */
    public function withHeaders(array $headers): self;

    /** @param resource $fp */
    public function logToResource($fp): self;

    /**
     * @throws CurlException
     */
    public function logToFile(string $logFilePath): self;

    /**
     * @param phpstanCurlOptions $options
     */
    public function createGetHandle(string $url, array $options = null): CurlConfigAwareHandleInterface;

    /**
     * @param array<string,string|int> $postData
     * @param phpstanCurlOptions       $options
     */
    public function createPostHandle(string $url, array $postData, array $options = []): CurlConfigAwareHandleInterface;
}
