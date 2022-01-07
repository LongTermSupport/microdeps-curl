<?php

declare(strict_types=1);

namespace MicroDeps\Curl;

/**
 * @phpstan-type  phpstanCurlOption array<int, string>|bool|float|int|resource|string
 * @phpstan-type  phpstanCurlOptions array<int, phpstanCurlOption>
 */
final class CurlOptionCollection
{
    public const OPTIONS_DEFAULT = [
        /*
         * true to follow any 'Location: ' header that the server sends as part of the HTTP header. See also CURLOPT_MAXREDIRS.
         */
        CURLOPT_FOLLOWLOCATION => true,

        /*
         * true to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
         */
        CURLOPT_RETURNTRANSFER => true,

        /*
         * The contents of the 'Accept-Encoding: ' header. This enables decoding of the response.
         * Supported encodings are 'identity', 'deflate', and 'gzip'.
         * If an empty string, '', is set, a header containing all supported encoding types is sent.
         */
        CURLOPT_ENCODING       => '',

        /*
         * Log the headers that are sent with the initial request and make available via curl_getinfo
         */
        CURLINFO_HEADER_OUT    => true,
    ];

    /**
     * @param phpstanCurlOptions $options
     */
    public function __construct(private array $options = self::OPTIONS_DEFAULT)
    {
    }

    /**
     * Set specific options, or call with no arguments to reset to the default.
     *
     * @param phpstanCurlOptions $options
     */
    public function set(array $options = null): self
    {
        $this->options = ($options ?? self::OPTIONS_DEFAULT);

        return $this;
    }

    /**
     * @param phpstanCurlOptions $options
     */
    public function update(array $options): self
    {
        if ([] === $options) {
            return $this;
        }
        // note, array_merge won't work due to numeric keys for curl options
        foreach ($options as $key => $value) {
            $this->options[$key] = $value;
        }

        return $this;
    }

    /** @return phpstanCurlOptions */
    public function get(): array
    {
        return $this->options;
    }

    /** @return phpstanCurlOption|null */
    public function getOption(int $key): mixed
    {
        return $this->options[$key] ?? null;
    }
}
