<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use Exception;
use RuntimeException;
use Throwable;

final class CurlException extends Exception
{
    public const MSG_RESPONSE_LOG_DIR_NOT_EXIST = 'Curl response log path %s does not exist';
    public const MSG_DIRECTORY_NOT_CREATED      = 'Directory "%s" was not created, error: %s';
    public const MSG_DIRECTORY_NOT_WRITABLE     = 'Directory "%s" is not writable, error: %s';
    public const MSG_INVALID_OPTIONS            = "Invalid Curl Option(s) %s, \nnote that your curl version is %s and it may be that the option requires a more up to date version of curl.";
    public const MSG_FAILED_WRITING_TO_LOG      = 'Failed writing to log: %s';
    public const MSG_FAILED_OPENING_LOG_FILE    = 'Failed opening log file path: %s';
    public const MSG_FAILED_REQUEST             = "Unsuccesful request to url: %s\nError: %s\nInfo: %s";

    private function __construct(string $message, Throwable $previous = null)
    {
        $message = $this->appendInfo($message);
        parent::__construct($message, 0, $previous);
    }

    public static function withFormat(string $format, string ...$values): self
    {
        return new self(sprintf($format, ...$values));
    }

    public static function withFormatAndPrevious(string $format, Throwable $previous, string ...$values): self
    {
        return new self(sprintf($format, ...$values), $previous);
    }

    private function appendInfo(string $message): string
    {
        $curlVersion = curl_version();
        if (false === $curlVersion) {
            throw new RuntimeException('Curl version not available, is curl actually installed?');
        }
        $curlFeatures = $curlVersion['features'];
        unset($curlVersion['features']);
        $version   = "\nCurl version: " . print_r($curlVersion, true);
        $features  = "\nFeatures:\n";
        $bitfields = [
            'CURL_VERSION_IPV6',
            'CURL_VERSION_KERBEROS4',
            'CURL_VERSION_SSL',
            'CURL_VERSION_LIBZ',
        ];
        foreach ($bitfields as $feature) {
            $features .= "\n - {$feature}: " . ((($curlFeatures & \constant($feature)) > 0) ? ' true' : ' false');
        }

        return "{$message}\n\n{$version}\n\n{$features}";
    }
}
