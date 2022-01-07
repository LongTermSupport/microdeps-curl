<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use MicroDeps\Exception\CurlException;

final class CurlExec
{
    private bool   $success;
    private string $response;
    /** @var array<string,string> */
    private array  $info;
    private string $error;

    /**
     * @throws CurlException
     */
    public function __construct(private CurlConfigAwareHandle $handle, private ?string $logResponseDirectory = null)
    {
        $rawHandle      = $this->handle->getHandle();
        $result         = curl_exec($rawHandle);
        $this->response = \is_string($result) ? $result : '';
        $this->info     = \is_array($info = curl_getinfo($rawHandle)) ? $info : [];
        $this->error    = curl_error($rawHandle);
        $this->success  = false !== $result && 200 === $this->info['http_code'];
        $this->log();
        $this->logResponse();
    }

    public function getInfoAsString(): string
    {
        return print_r($this->info, true);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return array<string,mixed>
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    public function getError(): string
    {
        return $this->error;
    }

    private function log(): void
    {
        $log = $this->handle->getOptions()->getOption(CURLOPT_STDERR);
        if (null === $log || !\is_resource($log)) {
            return;
        }
        fwrite($log, "\nCurl Info:\n" . $this->getInfoAsString() . "\n\n");
        if ('' !== $this->error) {
            fwrite($log, "\nCurl Error:\n" . $this->error . "\n\n");
        }
    }

    /**
     * @throws CurlException
     */
    private function logResponse(): void
    {
        if ('' === $this->logResponseDirectory || null === $this->logResponseDirectory) {
            return;
        }
        if (!is_dir($this->logResponseDirectory)) {
            throw CurlException::withFormat(
                CurlException::MSG_RESPONSE_LOG_DIR_NOT_EXIST,
                $this->logResponseDirectory
            );
        }
        $effectiveUrl = $this->info['url']          ?? 'no-url';
        $type         = $this->info['content_type'] ?? 'html';
        $extension    = match (true) {
            str_contains($type, 'json') => 'json',
            str_contains($type, 'javascript') => 'js',
            default => 'html'
        };
        $logFileName  = preg_replace('%[^a-z0-9]+%i', '_', $effectiveUrl) . '.' . $extension;
        file_put_contents("{$this->logResponseDirectory}/{$logFileName}", $this->response);
    }
}
