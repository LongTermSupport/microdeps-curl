<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

/**
 * @phpstan-import-type phpstanCurlOptions from CurlOptionCollection
 */
final class CurlHandleFactory
{
    /**
     * @param CurlOptionCollection $options
     */
    public function __construct(private CurlOptionCollection $options)
    {
    }

    public function insecure(): self
    {
        $this->updateOptions(
            [
                CURLOPT_SSL_VERIFYPEER   => false,
                CURLOPT_SSL_VERIFYSTATUS => false,
            ]
        );

        return $this;
    }

    /** @param phpstanCurlOptions $options */
    public function updateOptions(array $options): self
    {
        $this->options->update($options);

        return $this;
    }

    /**
     * @param string[] $headers
     */
    public function withHeaders(array $headers): self
    {
        return $this->updateOptions([CURLOPT_HEADER => $headers]);
    }

    /** @param resource $fp */
    public function logToResource($fp): self
    {
        return $this->updateOptions(
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
            if (!is_dir($logFileDir) && !mkdir($logFileDir, 0777, true) && !is_dir($logFileDir)) {
                throw CurlException::withFormat(CurlException::MSG_DIRECTORY_NOT_CREATED, $logFileDir);
            }
            if (!touch($logFilePath)) {
                throw CurlException::withFormat(CurlException::MSG_DIRECTORY_NOT_WRITABLE, $logFilePath);
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
    public function createGetHandle(?string $url, array $options = null): CurlConfigAwareHandle
    {
        if (null !== $options) {
            $this->updateOptions($options);
        }

        return new CurlConfigAwareHandle($url, $this->options);
    }
}
