<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
final class CurlHandleFactory
{
    private CurlOptionCollection $options;

    public function __construct(CurlOptionCollection $options = null)
    {
        $this->options = $options ?? new CurlOptionCollection();
    }

    public function insecure(): self
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
    public function withOptions(array $options): self
    {
        $this->options->update($options);

        return $this;
    }

    /**
     * @param string[] $headers
     */
    public function withHeaders(array $headers): self
    {
        return $this->withOptions([CURLOPT_HEADER => $headers]);
    }

    /** @param resource $fp */
    public function logToResource($fp): self
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
    public function logToFile(string $logFilePath): self
    {
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

    /**
     * @param phpstanCurlOptions $options
     */
    public function createGetHandle(string $url, array $options = null): CurlConfigAwareHandle
    {
        if (null !== $options) {
            $this->withOptions($options);
        }

        return new CurlConfigAwareHandle($url, $this->options);
    }
}
