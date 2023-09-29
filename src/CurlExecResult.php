<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use MicroDeps\Curl\Interface\CurlConfigAwareHandleInterface;
use MicroDeps\Curl\Testing\MockHandle;
use Safe\Exceptions\FilesystemException;

/**
 * @phpstan-type phpstanCurlInfo = array{
 *       url?: string,
 *       content_type?: null|string,
 *       http_code?: integer,
 *       header_size?: integer,
 *       request_size?: integer,
 *       filetime?: integer,
 *       ssl_verify_result?: integer,
 *       redirect_count?: integer,
 *       total_time?: float,
 *       namelookup_time?: float,
 *       connect_time?: float,
 *       pretransfer_time?: float,
 *       size_upload?: float,
 *       size_download?: float,
 *       speed_download?: float,
 *       speed_upload?: float,
 *       download_content_length?: float,
 *       upload_content_length?: float,
 *       starttransfer_time?: float,
 *       redirect_time?: float,
 *       redirect_url?: string,
 *       primary_ip?: string,
 *       certinfo?: array<int,array<string,string>>,
 *       primary_port?: integer,
 *       local_ip?: string,
 *       local_port?: integer,
 *       http_version?: integer,
 *       protocol?: integer,
 *       ssl_verifyresult?: integer,
 *       scheme?: string,
 *       effective_method?: string
 * }
 */
final class CurlExecResult
{
    private bool $success;
    private string $response;
    /**
     * @var phpstanCurlInfo
     */
    private array $info;
    private string $error;

    /**
     * @throws CurlException
     */
    private function __construct(
        private CurlConfigAwareHandleInterface $handle,
        private ?string $logResponseDirectory = null
    ) {
        if ($this->handle instanceof MockHandle) {
            $this->response = $this->handle->response;
            $this->info     = $this->handle->info;
            $this->error    = $this->handle->error;
            $this->success  = $this->handle->success;

            return;
        }
        $rawHandle      = $this->handle->getHandle();
        $result         = curl_exec($rawHandle);
        $this->response = \is_string($result) ? $result : '';
        $this->info     = \is_array($info = \Safe\curl_getinfo($rawHandle)) ? $info : [];
        $this->error    = curl_error($rawHandle);
        $this->success  = (false !== $result) && (200 === ($this->info['http_code'] ?? false));
        $this->log();
        $this->logResponse();
    }

    /**
     * Will return the result on success or failure.
     *
     * @throws CurlException
     */
    public static function try(
        CurlConfigAwareHandleInterface $handle,
        string $logResponseDirectory = null
    ): self {
        return new self($handle, $logResponseDirectory);
    }

    /**
     * Will return a successful result or throw a CurlException.
     *
     * @throws CurlException
     */
    public static function exec(
        CurlConfigAwareHandleInterface $handle,
        string $logResponseDirectory = null
    ): self {
        $result = self::try($handle, $logResponseDirectory);
        if (false === $result->isSuccess()) {
            throw CurlException::withFormat(
                CurlException::MSG_FAILED_REQUEST,
                $handle->url ?? 'no-url',
                $result->getError(),
                $result->getInfoAsString()
            );
        }

        return $result;
    }

    public function getInfoAsString(): string
    {
        return "Info:\n" .
               print_r($this->info, true) .
               "\n\nHandle Options:" .
               print_r($this->handle->getOptions()->getOptionsDebug(), true);
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
     * @return phpstanCurlInfo
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @throws CurlException
     */
    private function log(): void
    {
        $log = $this->handle->getOptions()->getOption(CURLOPT_STDERR);
        if (null === $log || !\is_resource($log)) {
            return;
        }
        $data = "\nCurl Info:\n" . $this->getInfoAsString() . "\n\n";
        try {
            \Safe\fwrite($log, $data);
        } catch (FilesystemException $e) {
            throw CurlException::withFormatAndPrevious(CurlException::MSG_FAILED_WRITING_TO_LOG, $e, $data);
        }
        if ('' === $this->error) {
            return;
        }
        $data = "\nCurl Error:\n" . $this->error . "\n\n";
        try {
            \Safe\fwrite($log, $data);
        } catch (FilesystemException $e) {
            throw CurlException::withFormatAndPrevious(CurlException::MSG_FAILED_WRITING_TO_LOG, $e, $data);
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
            str_contains($type, 'json')       => 'json',
            str_contains($type, 'javascript') => 'js',
            default                           => 'html'
        };
        /** @var @phpstan-ignore-next-line it is a string, never an array */
        $logFileName = \Safe\preg_replace('%[^a-z0-9]+%i', '_', $effectiveUrl) . '.' . $extension;
        \Safe\file_put_contents("{$this->logResponseDirectory}/{$logFileName}", $this->response);
    }
}
