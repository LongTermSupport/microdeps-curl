<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Testing;

use CurlHandle;
use MicroDeps\Curl\CurlExecResult;
use MicroDeps\Curl\CurlOptionCollection;
use MicroDeps\Curl\Interface\CurlConfigAwareHandleInterface;
use RuntimeException;

/**
 * @phpstan-import-type phpstanCurlInfo from CurlExecResult
 */
final class MockHandle implements CurlConfigAwareHandleInterface
{
    public string $response = '';
    public bool $success    = true;
    public string $error    = '';
    /** @var phpstanCurlInfo */
    public array $info = [];

    public function __construct(public readonly string $url, private readonly CurlOptionCollection $options) {}

    public function getHandle(): CurlHandle
    {
        throw new RuntimeException('Unsupported, the mock doesnt really have a curl handle');
    }

    public function getOptions(): CurlOptionCollection
    {
        return $this->options;
    }
}
