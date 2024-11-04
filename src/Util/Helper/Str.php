<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use Laminas\Diactoros\Stream;
use PhoneBurner\SaltLite\Framework\Domain\RegExp;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

abstract class Str
{
    private const string TRIM_CHARS = " \t\n\r\0\x0B";

    private const array TOKEN_PATTERN = [
        '/[_\.\-\s]+/',
        '/(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})/',
        '/(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})/',
    ];
    private const array TOKEN_REPLACEMENT = ['_', '_\1', '_\1'];

    public static function stringable(mixed $string): bool
    {
        return \is_string($string)
            || $string instanceof Stringable
            || (\is_object($string) && \method_exists($string, '__toString'));
    }

    /**
     * Returns the passed argument if it is a string or converts it to a string
     * if it is an instance of `Stringable`. For legacy compatibility with things
     * like PSR interfaces that have not added the new PHP 8 interface, we also
     * check if the argument is an object that implements `__toString`.
     */
    public static function string(mixed $string): string
    {
        if (self::stringable($string)) {
            return (string)$string;
        }

        throw new \InvalidArgumentException('$string Must Be String, Stringable, or Implement __toString');
    }

    /**
     * The inverse of `Str::string`; convert a string to a `Stringable` object
     * or return the object if it is already an instance of `Stringable`.
     */
    public static function object(string|\Stringable $string): Stringable
    {
        return $string instanceof Stringable ? $string : new class ($string) implements Stringable {
            public function __construct(private readonly string $string)
            {
            }

            public function __toString(): string
            {
                return $this->string;
            }
        };
    }

    /**
     * Convert a string or `Stringable` object to an instance of StreamInterface.
     * If the argument is an instance of StreamInterface, which implements
     * `__toString`, we return that same instance. For backwards compatibility,
     * this will also handle objects that do not implement the PHP 8 `Stringable`
     * interface or our polyfill, but do implement `__toString`.
     *
     * @param string|Stringable|StreamInterface $string
     */
    public static function stream($string = ''): StreamInterface
    {
        if ($string instanceof StreamInterface) {
            return $string;
        }

        $resource = \fopen('php://temp', 'rb+');
        if ($resource === false) {
            throw new RuntimeException('Could Not Create Stream');
        }

        \fwrite($resource, self::string($string));
        \rewind($resource);

        return new Stream($resource);
    }

    /**
     * Trim whitespace characters from both sides of a given string. An array of
     * additional characters to trim off can be passed as the second parameter.
     */
    public static function trim(string $string, array $additional_chars = []): string
    {
        return \trim($string, self::TRIM_CHARS . \implode('', $additional_chars));
    }

    /**
     * Trim whitespace characters from the right side of a string. An array of
     * additional characters to trim off can be passed as the second parameter.
     */
    public static function rtrim(string $string, array $additional_chars = []): string
    {
        return \rtrim($string, self::TRIM_CHARS . \implode('', $additional_chars));
    }

    /**
     * Trim whitespace characters from the left side of a string. An array of
     * additional characters to trim off can be passed as the second parameter.
     */
    public static function ltrim(string $string, array $additional_chars = []): string
    {
        return \ltrim($string, self::TRIM_CHARS . \implode('', $additional_chars));
    }

    public static function truncate(
        string|Stringable $string,
        int $max_length = 80,
        string $trim_marker = '...',
    ): string {
        $max_length >= 0 || throw new \UnexpectedValueException('Max Length Must Be Non-Negative');
        \strlen($trim_marker) <= $max_length || throw new \UnexpectedValueException('Trim Marker Length Must Be Less Than or Equal to Max Length');

        return \mb_strimwidth((string)$string, 0, $max_length, $trim_marker);
    }

    /**
     * Determine if a string contains a given substring, with the behavior of
     * the new PHP 8 method `str_contains`. This means that the method will
     * always return true if the `$needle` is an empty string.
     */
    public static function contains(string $haystack, string $needle, bool $case_sensitive = true): bool
    {
        if ($needle === '') {
            return true;
        }

        return false !== ($case_sensitive ? \strpos($haystack, $needle) : \stripos($haystack, $needle));
    }

    /**
     * Determine if a string starts with a given substring, with the behavior of
     * the new PHP 8 method `str_starts_with`. This means that the method will
     * always return true if the `$needle` is an empty string.
     */
    public static function startsWith(string $haystack, string $needle, bool $case_sensitive = true): bool
    {
        if ($needle === '') {
            return true;
        }

        return 0 === ($case_sensitive ? \strpos($haystack, $needle) : \stripos($haystack, $needle));
    }

    /**
     * Determine if a string ends with a given substring, with the behavior of
     * the new PHP 8 method `str_ends_with`. This means that the method will
     * always return true if the `$needle` is an empty string.
     */
    public static function endsWith(string $haystack, string $needle, bool $case_sensitive = true): bool
    {
        if ($needle === '') {
            return true;
        }

        $length = \strlen($needle);
        return 0 === \substr_compare($haystack, $needle, -$length, $length, ! $case_sensitive);
    }

    /**
     * Concatenate the `$prefix` string to the start of the `$string` string, if
     * the `$string` does not already start with the `$prefix`, e.g.:
     *    Str::start("path/to/something", "/"); // "/path/to/something"
     *    Str::start("/path/to/something", "/"); // "/path/to/something"
     */
    public static function start(string $string, string $prefix): string
    {
        return self::startsWith($string, $prefix) ? $string : $prefix . $string;
    }

    /**
     * Concatenate the `$prefix` string to the end of the `$string` string, if
     * the `$string` does not already end with the `$prefix`, e.g.:
     *    Str::start("path/to/something", "/"); // "path/to/something/"
     *    Str::start("path/to/something/", "/"); // "path/to/something/"
     */
    public static function end(string $string, string $suffix): string
    {
        return self::endsWith($string, $suffix) ? $string : $string . $suffix;
    }

    public static function strip(string $string, RegExp|string $search): string
    {
        if (\is_string($search)) {
            return \str_replace($search, '', $string);
        }

        $result = \preg_replace((string)$search, '', $string);
        if ($result === null) {
            // https://www.php.net/manual/en/pcre.constants.php
            throw new \RuntimeException('preg_replace() returned error code ' . \preg_last_error());
        }
        return $result;
    }

    /**
     * Takes a fully qualified, qualified, relative, or unqualified class name
     * and returns the unqualified name of the class without the namespace.
     */
    public static function shortname(string $classname): string
    {
        if (! \str_contains($classname, '\\')) {
            return $classname;
        }

        return \ltrim((string)\strrchr($classname, '\\'), '\\');
    }

    private static function tokenize(string $string): array
    {
        $string = self::trim($string, ['-', '_', '.']);
        $string = \preg_replace(self::TOKEN_PATTERN, self::TOKEN_REPLACEMENT, $string);
        $string = \strtolower((string)$string);

        return \explode('_', $string);
    }

    public static function snake(string $string): string
    {
        return \implode('_', self::tokenize($string));
    }

    public static function kabob(string $string): string
    {
        return \implode('-', self::tokenize($string));
    }

    public static function pascal(string $string): string
    {
        return \implode('', \array_map(\ucfirst(...), self::tokenize($string)));
    }

    public static function camel(string $string): string
    {
        return \lcfirst(self::pascal($string));
    }

    public static function screaming(string $string): string
    {
        return \strtoupper(self::snake($string));
    }

    public static function dot(string $string): string
    {
        return \implode('.', self::tokenize($string));
    }
}
