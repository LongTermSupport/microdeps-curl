<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use MicroDeps\Curl\Interface\CurlHandleFactoryInterface;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
abstract class AbstractCurlHandleFactory implements CurlHandleFactoryInterface
{
    protected CurlOptionCollection $options;

    public function __construct(CurlOptionCollection $options = null)
    {
        $this->options = $options ?? new CurlOptionCollection();
    }

    public function insecure(): CurlHandleFactoryInterface
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => 0,
        ];
        if (\defined('CURLOPT_SSL_VERIFYSTATUS')) {
            $options += [CURLOPT_SSL_VERIFYSTATUS => 0];
        }
        if (\defined('CURLOPT_SSL_VERIFYHOST')) {
            $options += [CURLOPT_SSL_VERIFYHOST => 0];
        }

        return $this->withOptions($options);
    }

    /** @param phpstanCurlOptions $options */
    public function withOptions(array $options): CurlHandleFactoryInterface
    {
        $this->options->update($options);

        return $this;
    }

    /**
     * @param string[] $headers
     */
    public function withHeaders(array $headers): CurlHandleFactoryInterface
    {
        return $this->withOptions([CURLOPT_HEADER => $headers]);
    }

    /** @param resource $fp */
    public function logToResource($fp): CurlHandleFactoryInterface
    {
        return $this->withOptions(
            [
                /*
                 * The following two options are mutually exclusive,
                 * you must set header out to false for verbose to be true
                 */
                CURLOPT_VERBOSE     => true,
                CURLINFO_HEADER_OUT => false,
                CURLOPT_STDERR      => $fp,
            ]
        );
    }

    /**
     * @throws CurlException
     */
    public function logToFile(string $logFilePath): CurlHandleFactoryInterface
    {
        if ('' === $logFilePath) {
            return $this;
        }
        if (!is_writable($logFilePath)) {
            $logFileDir = \dirname($logFilePath);
            if (!is_dir($logFileDir) && !@mkdir($logFileDir, 0755, true) && !is_dir($logFileDir)) {
                $error = error_get_last();
                throw CurlException::withFormat(
                    CurlException::MSG_DIRECTORY_NOT_CREATED,
                    $logFileDir,
                    $error['message'] ?? 'unknown error'
                );
            }
            if (!@touch($logFilePath)) {
                $error = error_get_last();
                throw CurlException::withFormat(
                    CurlException::MSG_DIRECTORY_NOT_WRITABLE,
                    $logFileDir,
                    $error['message'] ?? 'unknown error'
                );
            }
        }

        $resource = fopen($logFilePath, 'ab+');
        if (false === $resource) {
            throw CurlException::withFormat(CurlException::MSG_FAILED_OPENING_LOG_FILE, $logFilePath);
        }

        return $this->logToResource($resource);
    }
}
