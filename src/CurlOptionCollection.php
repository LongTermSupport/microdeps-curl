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
         * true to follow any 'Location: ' header that the server sends as part of the HTTP header.
         * See also CURLOPT_MAXREDIRS.
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
     * These are special options that don't start with CURLOPT for whatever reason.
     */
    private const SPECIAL_OPTS = [
        'CURLINFO_HEADER_OUT',
    ];

    /** @var array<int,string> */
    private static array $validOptions = [];

    /**
     * The configuration with constant names for keys instead of ints.
     *
     * @var array<string, phpstanCurlOption>
     */
    private array $optionsDebug = [];

    /** @var phpstanCurlOptions */
    private $options;

    /**
     * @param phpstanCurlOptions $options
     */
    public function __construct(array $options = self::OPTIONS_DEFAULT)
    {
        $this->set($options);
    }

    /** @return array<int,string> */
    public static function validOptions(): array
    {
        if ([] === self::$validOptions) {
            $curlOptions = get_defined_constants(true)['curl'];
            $curlOptions = array_filter(
                $curlOptions,
                static fn($key) => str_starts_with($key, 'CURLOPT_')
                                   || \in_array($key, self::SPECIAL_OPTS, true),
                ARRAY_FILTER_USE_KEY
            );
            // note, as we do the flip, we aggregate some aliased options.
            // There are some options with multiple constants that point to the same int value
            /** @var array<int, string> $curlOptions */
            $curlOptions        = array_flip($curlOptions);
            self::$validOptions = $curlOptions;
        }

        return self::$validOptions;
    }

    /**
     * Set specific options, or call with no arguments to reset to the default.
     *
     * @param phpstanCurlOptions $options
     */
    public function set(array $options = null): self
    {
        $this->options = $this->optionsDebug = [];
        $this->update($options ?? self::OPTIONS_DEFAULT);

        return $this;
    }

    /**
     * @param phpstanCurlOptions $options
     *
     * @throws CurlException
     */
    public function update(array $options): self
    {
        if ([] === $options) {
            return $this;
        }
        self::validOptions();
        // note, array_merge won't work due to numeric keys for curl options
        $invalid = [];
        foreach ($options as $key => $value) {
            if (is_string($key)) {
                throw new \InvalidArgumentException("
                you have set an option with a string key $key, 
                instead you should be using teh actual curl constant - not its name as a string
                ");
            }
            if (!isset(self::$validOptions[$key])) {
                $invalid[$key] = $value;
                continue;
            }
            $this->options[$key]                           = $value;
            $this->optionsDebug[self::$validOptions[$key]] = $value;
        }
        if ([] !== $invalid) {
            throw CurlException::withFormat(
                CurlException::MSG_INVALID_OPTIONS,
                print_r($invalid, true),
                /* @phpstan-ignore-next-line confused by curl_version return type */
                curl_version()['version']
            );
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

    /** @return array<string, phpstanCurlOption> */
    public function getOptionsDebug(): array
    {
        return $this->optionsDebug;
    }
}
