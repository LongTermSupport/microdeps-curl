<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

use Exception;
use Throwable;

final class CurlException extends Exception
{
    public const MSG_RESPONSE_LOG_DIR_NOT_EXIST = 'Curl response log path %s does not exist';
    public const MSG_DIRECTORY_NOT_CREATED      = 'Directory "%s" was not created';
    public const MSG_DIRECTORY_NOT_WRITABLE     = 'Directory "%s" is not writable';
    public const MSG_INVALID_OPTIONS            = 'Invalid Curl Option(s) %s';
    public const MSG_FAILED_WRITING_TO_LOG      = 'Failed writing to log: %s';
    public const MSG_FAILED_OPENING_LOG_FILE    = 'Failed opening log file path: %s';

    private function __construct(string $message, Throwable $previous = null)
    {
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
}
