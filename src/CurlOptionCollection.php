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

        /*
         * true to fail verbosely if the HTTP code returned is greater than or equal to 400.
         * The default behavior is to return the page normally, ignoring the code.
         */
        CURLOPT_FAILONERROR    => true,
    ];

    /** @var array<int,string> */
    public readonly array $validOptions;

    /**
     * The configuration with constant names for keys instead of ints
     *
     * @var array<string, phpstanCurlOption>
     */
    private array $optionsDebug = [];

    /**
     * @param phpstanCurlOptions $options
     */
    public function __construct(private array $options = self::OPTIONS_DEFAULT)
    {
        $this->validOptions = array_flip(get_defined_constants(true)['curl']);
        $this->updateOptionsDebug();
    }

    private function updateOptionsDebug(): void
    {
        $this->optionsDebug = [];
        foreach ($this->options as $int => $val) {
            $this->optionsDebug[$this->validOptions[$int]] = $val;
        }
    }

    /**
     * Set specific options, or call with no arguments to reset to the default.
     *
     * @param phpstanCurlOptions $options
     */
    public function set(array $options = null): self
    {
        $this->options = ($options ?? self::OPTIONS_DEFAULT);
        $this->updateOptionsDebug();

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
        $this->updateOptionsDebug();

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
