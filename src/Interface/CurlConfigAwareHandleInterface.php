<?php

declare(strict_types=1);

namespace MicroDeps\Curl\Interface;

use CurlHandle;
use MicroDeps\Curl\CurlOptionCollection;

/**
 * This is a very simple wrapper that allows us to keep track of config for a specific CurlHandle.
 */
interface CurlConfigAwareHandleInterface
{
    public function __construct(string $url, CurlOptionCollection $options);

    public function getHandle(): CurlHandle;

    public function getOptions(): CurlOptionCollection;
}
