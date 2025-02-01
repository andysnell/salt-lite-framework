<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use Generator;
use Laminas\Diactoros\StreamFactory;
use PhoneBurner\SaltLite\Framework\Domain\RegExp;
use PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture\ShinyThing;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use stdClass;
use Stringable;

final class StrTest extends TestCase
{
    #[DataProvider('providesValidStringTestCases')]
    #[Test]
    public function stringable_will_return_true_for_strings_and_stringable_objects(
        string $expected,
        string|\Stringable $test,
    ): void {
        self::assertTrue(Str::stringable($test));
    }

    #[DataProvider('providesInvalidStringTestCases')]
    #[Test]
    public function stringable_will_return_false_for_non_strings_or_stringable_objects(mixed $test): void
    {
        self::assertFalse(Str::stringable($test));
    }

    #[DataProvider('providesValidStringTestCases')]
    #[Test]
    public function string_will_cast_stringlike_thing_to_string(string $expected, string|\Stringable $test): void
    {
        self::assertSame($expected, Str::cast($test));
    }

    #[Test]
    public function stream_will_return_passed_instance_if_StreamInterface(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        self::assertSame($stream, Str::stream($stream));
    }

    #[Test]
    public function stream_default_value_returns_empty_Stream(): void
    {
        self::assertSame('', (string)Str::stream());
    }

    #[DataProvider('providesValidStringTestCases')]
    #[Test]
    public function stream_will_cast_string_or_stringable_to_stream(string $expected, string|Stringable $test): void
    {
        $stream = Str::stream($test);

        self::assertInstanceOf(StreamInterface::class, $stream);
        self::assertSame($expected, $stream->getContents());
    }

    public static function providesValidStringTestCases(): Generator
    {
        yield 'string' => ['Hello, World', 'Hello, World'];

        yield Stringable::class => ['One Two Three', new class implements Stringable {
            public function __toString(): string
            {
                return 'One Two Three';
            }
        },];

        yield '__toString' => ['Foo Bar Baz', new class {
            public function __toString(): string
            {
                return "Foo Bar Baz";
            }
        },];

        $test = 'StreamInterface Implements __toString';
        yield 'stream' => [$test, (new StreamFactory())->createStream($test)];
    }

    public static function providesInvalidStringTestCases(): Generator
    {
        yield 'null' => [null];
        yield 'true' => [true];
        yield 'false' => [false];
        yield 'zero' => [0];
        yield 'int' => [1];
        yield 'float' => [1.2];
        yield 'object' => [new stdClass()];
        yield 'empty_array' => [[]];
        yield 'array' => [['foo' => 'bar', 'baz' => 'quz']];

        $resource = \fopen('php://temp', 'rb+') ?: self::fail();
        \fwrite($resource, 'Hello World');
        \rewind($resource);

        yield 'resource' => [$resource];
    }

    #[DataProvider('providesTrimTestCases')]
    #[Test]
    public function trim_will_trim_whitespace_characters(array $test): void
    {
        $trimmed = Str::trim($test['input']);
        self::assertSame($test['trim'], $trimmed);
    }

    #[DataProvider('providesAdditionalCharacterTrimTestCases')]
    #[Test]
    public function trim_will_trim_additional_characters(array $test): void
    {
        $trimmed = Str::trim($test['input'], $test['characters']);
        self::assertSame($test['trim'], $trimmed);
    }

    #[DataProvider('providesTrimTestCases')]
    #[Test]
    public function rtrim_will_trim_whitespace_characters(array $test): void
    {
        $trimmed = Str::rtrim($test['input']);
        self::assertSame($test['rtrim'], $trimmed);
    }

    #[DataProvider('providesAdditionalCharacterTrimTestCases')]
    #[Test]
    public function rtrim_will_trim_additional_characters(array $test): void
    {
        $trimmed = Str::rtrim($test['input'], $test['characters']);
        self::assertSame($test['rtrim'], $trimmed);
    }

    #[DataProvider('providesTrimTestCases')]
    #[Test]
    public function ltrim_will_trim_whitespace_characters(array $test): void
    {
        $trimmed = Str::ltrim($test['input']);
        self::assertSame($test['ltrim'], $trimmed);
    }

    #[DataProvider('providesAdditionalCharacterTrimTestCases')]
    #[Test]
    public function ltrim_will_trim_additional_characters(array $test): void
    {
        $trimmed = Str::ltrim($test['input'], $test['characters']);
        self::assertSame($test['ltrim'], $trimmed);
    }

    public static function providesTrimTestCases(): Generator
    {
        yield 'no_trim' => [[
            'input' => 'Hello, World!',
            'trim' => 'Hello, World!',
            'rtrim' => 'Hello, World!',
            'ltrim' => 'Hello, World!',
        ],];

        yield 'spaces_trim' => [[
            'input' => '  Hello, World!  ',
            'trim' => 'Hello, World!',
            'rtrim' => '  Hello, World!',
            'ltrim' => 'Hello, World!  ',
        ],];

        yield 'line_breaks_trim' => [[
            'input' => "\n\n\r Hello, World! \r\r\n",
            'trim' => "Hello, World!",
            'rtrim' => "\n\n\r Hello, World!",
            'ltrim' => "Hello, World! \r\r\n",
        ],];

        yield 'all_the_whitespace' => [[
            'input' => " \t\n\r\0\x0B \t\n\r\0\x0BHello, \t\n\r\0\x0B World! \t\n\r\0\x0B \t\n\r\0\x0B",
            'trim' => "Hello, \t\n\r\0\x0B World!",
            'rtrim' => " \t\n\r\0\x0B \t\n\r\0\x0BHello, \t\n\r\0\x0B World!",
            'ltrim' => "Hello, \t\n\r\0\x0B World! \t\n\r\0\x0B \t\n\r\0\x0B",
        ],];
    }

    public static function providesAdditionalCharacterTrimTestCases(): Generator
    {
        foreach (self::providesTrimTestCases() as $test_name => $test) {
            $test[0]['characters'] = [];
            yield $test_name . '_no_chars' => $test;
        }

        yield 'trim_everything' => [[
            'characters' => \str_split('Hello, World!'),
            'input' => 'Hello, World!',
            'trim' => '',
            'rtrim' => '',
            'ltrim' => '',
        ],];

        yield 'trim_almost_everything' => [[
            'characters' => \str_split('Hello, World!'),
            'input' => 'Hello, | World!',
            'trim' => '|',
            'rtrim' => 'Hello, |',
            'ltrim' => '| World!',
        ],];

        yield 'all_the_whitespace_with_symbol' => [[
            'characters' => ['$'],
            'input' => " \t\n\r\0\x0B \t\n\r\0\x0B$12.42\t\n\r\0\x0B \t\n\r\0\x0B",
            'trim' => "12.42",
            'rtrim' => " \t\n\r\0\x0B \t\n\r\0\x0B$12.42",
            'ltrim' => "12.42\t\n\r\0\x0B \t\n\r\0\x0B",
        ],];

        yield 'trim_quotes_single' => [[
            'characters' => ['"', "'"],
            'input' => '\'Hello, World!\'',
            'trim' => 'Hello, World!',
            'rtrim' => '\'Hello, World!',
            'ltrim' => 'Hello, World!\'',
        ],];

        yield 'trim_quotes_double' => [[
            'characters' => ['"', "'"],
            'input' => '"Hello, World!"',
            'trim' => 'Hello, World!',
            'rtrim' => '"Hello, World!',
            'ltrim' => 'Hello, World!"',
        ],];
    }

    #[DataProvider('providesContainsTestCases')]
    #[Test]
    public function contains_returns_if_string_contains_string(array $test): void
    {
        self::assertSame($test['expected'], Str::contains(
            $test['haystack'],
            $test['needle'],
            $test['case_sensitive'],
        ));
    }

    public static function providesContainsTestCases(): Generator
    {
        $test = static function (bool $expected, bool $case_sensitive, string $needle, string|null $haystack = null): array {
            $haystack ??= 'The Quick Brown Fox Jumped Over The Lazy Dog.';
            return [['haystack' => $haystack, 'expected' => $expected, 'needle' => $needle, 'case_sensitive' => $case_sensitive]];
        };

        // PHP 8's new str_contains function always returns true when needle is empty
        yield $test(true, true, '');
        yield $test(true, true, 'Brown Fox');
        yield $test(true, true, 'The Quick Brown Fox');
        yield $test(true, true, 'The Quick Brown Fox Jumped Over The Lazy Dog.');
        yield $test(true, true, 'The Lazy Dog.');
        yield $test(false, true, 'BROWN FOX');
        yield $test(false, true, 'THE QUICK BROWN FOX');
        yield $test(false, true, 'THE QUICK BROWN FOX JUMPED OVER THE LAZY DOG.');
        yield $test(false, true, 'THE LAZY DOG.');
        yield $test(false, true, 'brown fox');
        yield $test(false, true, 'the quick brown fox');
        yield $test(false, true, 'the quick brown fox jumped over the lazy dog.');
        yield $test(false, true, 'the lazy dog.');
        yield $test(true, false, 'BROWN FOX');
        yield $test(true, false, 'THE QUICK BROWN FOX');
        yield $test(true, false, 'THE QUICK BROWN FOX JUMPED OVER THE LAZY DOG.');
        yield $test(true, false, 'THE LAZY DOG.');
        yield $test(true, false, 'brown fox');
        yield $test(true, false, 'the quick brown fox');
        yield $test(true, false, 'the quick brown fox jumped over the lazy dog.');
        yield $test(true, false, 'the lazy dog.');
        yield $test(false, false, 'quick fox');
        yield $test(false, true, 'quick fox');
        yield $test(false, false, 'QUICK FOX');
        yield $test(false, true, 'QUICK FOX');
        yield $test(false, false, 'foo', '');
        yield $test(true, true, ' ');
        yield $test(true, false, ' ');
        yield $test(false, false, 'foo', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(false, true, 'foo', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, true, '😺 💷 📅', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, false, '😺 💷 📅', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, true, '🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, false, '🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(false, true, '🐌 🅾️ 😺 💷 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(false, false, '🐌 🅾️ 😺 💷 📅 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, false, '');
        yield $test(true, true, '', '');
        yield $test(true, false, '', '');
        yield $test(true, true, '', 'foo');
        yield $test(true, false, '', 'foo');
    }

    #[DataProvider('providesStartsWithTestCases')]
    #[Test]
    public function startsWith_returns_if_string_starts_with_string(array $test): void
    {
        self::assertSame($test['expected'], Str::startsWith(
            $test['haystack'],
            $test['needle'],
            $test['case_sensitive'],
        ));
    }

    public static function providesStartsWithTestCases(): Generator
    {
        $test = static function (bool $expected, bool $case_sensitive, string $needle, string|null $haystack = null): array {
            $haystack ??= 'The Quick Brown Fox Jumped Over The Lazy Dog.';
            return [['haystack' => $haystack, 'expected' => $expected, 'needle' => $needle, 'case_sensitive' => $case_sensitive]];
        };

        // PHP 8's new string_ends_with function always returns true when needle is empty
        yield $test(true, true, '');
        yield $test(true, true, 'T');
        yield $test(true, true, 'The');
        yield $test(true, true, 'The Quick Brown Fox');
        yield $test(true, true, 'The Quick Brown Fox Jumped Over The Lazy Dog.');
        yield $test(true, false, 'T');
        yield $test(true, false, 'The');
        yield $test(true, false, 'The Quick Brown Fox');
        yield $test(true, false, 'The Quick Brown Fox Jumped Over The Lazy Dog.');
        yield $test(true, false, 't');
        yield $test(true, false, 'the');
        yield $test(true, false, 'the quick brown fox');
        yield $test(true, false, 'the quick brown fox jumped over the lazy dog.');
        yield $test(false, true, 't');
        yield $test(false, true, 'the');
        yield $test(false, true, 'the quick brown fox');
        yield $test(false, true, 'the quick brown fox jumped over the lazy dog.');
        yield $test(true, true, '🍩 🏒 🎯 🍣 ⏳ 📀 🐌', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(true, false, '🍩 🏒 🎯 🍣 ⏳ 📀 🐌', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(false, true, '🏒 🎯 🍣 ⏳ 📀 🐌', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
        yield $test(false, false, '🏒 🎯 🍣 ⏳ 📀 🐌', '🍩 🏒 🎯 🍣 ⏳ 📀 🐌 🅾️ 😺 💷 📅 🔋 🌴 ⛷ 💣 💚 🌄 ⚡️ ⚫️ ↙️');
    }

    #[DataProvider('providesEndsWithTestCases')]
    #[Test]
    public function endsWith_returns_if_string_ends_with_string(array $test): void
    {
        self::assertSame($test['expected'], Str::endsWith(
            $test['haystack'],
            $test['needle'],
            $test['case_sensitive'],
        ));
    }

    public static function providesEndsWithTestCases(): Generator
    {
        $test = static function (bool $expected, bool $case_sensitive, string $needle, string|null $haystack = null): array {
            $haystack ??= 'The Quick Brown Fox Jumped Over The Lazy Dog.';
            return [['haystack' => $haystack, 'expected' => $expected, 'needle' => $needle, 'case_sensitive' => $case_sensitive]];
        };

        // PHP 8's new string_ends_with function always returns true when needle is empty
        yield $test(true, true, '');
        yield $test(true, true, '.');
        yield $test(true, true, 'Dog.');
        yield $test(true, true, 'Lazy Dog.');
        yield $test(true, true, 'The Quick Brown Fox Jumped Over The Lazy Dog.');
        yield $test(true, false, '');
        yield $test(true, false, '.');
        yield $test(true, false, 'DoG.');
        yield $test(true, false, 'LAZY dog.');
        yield $test(false, true, 'DoG.');
        yield $test(false, true, 'LAZY dog.');
        yield $test(false, true, ' The Quick Brown Fox Jumped Over The Lazy Dog.');
        yield $test(true, false, '', '');
        yield $test(true, true, '', '');
        yield $test(true, false, '', 'foo');
        yield $test(true, true, '', '');
        yield $test(true, true, '😃', 'Hello, World! 😃');
        yield $test(true, true, '! 👻😃👻', 'Hello, World! 👻😃👻');
        yield $test(false, true, '! 👻😃👻', 'Hello, World! 👻😀👻'); // slightly different emoji
        yield $test(true, false, 'D! 👻😃👻', 'Hello, World! 👻😃👻');
    }

    #[DataProvider('providesStartTestCases')]
    #[Test]
    public function start_prepends_if_string_does_not_start_with_value(array $test): void
    {
        self::assertSame($test['expected'], Str::start($test['input'], $test['prefix']));
    }

    public static function providesStartTestCases(): Generator
    {
        $test = static fn($input, $prefix, $expected): array => [['input' => $input, 'prefix' => $prefix, 'expected' => $expected]];

        yield $test('', '', '');
        yield $test('/path/to/something', '/', '/path/to/something');
        yield $test('path/to/something', '/', '/path/to/something');
        yield $test('https://www.example.com', 'https://www.example.com', 'https://www.example.com');
        yield $test('', 'https://www.example.com', 'https://www.example.com');
        yield $test('www.example.com', 'https://', 'https://www.example.com');
        yield $test('https://www.example.com', '', 'https://www.example.com');
        yield $test('📍️🎳📡😘🏕', '📍️🎳📡😘🏕', '📍️🎳📡😘🏕');
        yield $test('📡😘🏕', '📍️🎳', '📍️🎳📡😘🏕');
    }

    #[DataProvider('providesEndTestCases')]
    #[Test]
    public function end_appends_if_string_does_not_end_with_value(array $test): void
    {
        self::assertSame($test['expected'], Str::end($test['input'], $test['suffix']));
    }

    public static function providesEndTestCases(): Generator
    {
        $test = static fn($input, $suffix, $expected): array => [['input' => $input, 'suffix' => $suffix, 'expected' => $expected]];

        yield $test('', '', '');
        yield $test('path/to/something', '/', 'path/to/something/');
        yield $test('path/to/something/', '/', 'path/to/something/');
        yield $test('/path/to/something//', '/', '/path/to/something//');
        yield $test('https://www.example.com/', 'https://www.example.com/', 'https://www.example.com/');
        yield $test('', 'https://www.example.com', 'https://www.example.com');
        yield $test('www.example.com', '/path?query=foo', 'www.example.com/path?query=foo');
        yield $test('www.example.com/path?query=foo', '/path?query=foo', 'www.example.com/path?query=foo');
        yield $test('https://www.example.com', '', 'https://www.example.com');
        yield $test('⏬🎨💠🗒🔲🐊', '🏵🚾💂🍶📟🔍', '⏬🎨💠🗒🔲🐊🏵🚾💂🍶📟🔍');
        yield $test('⏬🎨💠🗒🔲🐊', '🐊', '⏬🎨💠🗒🔲🐊');
        yield $test('⏬🎨💠🗒🔲🐊 ', '🐊', '⏬🎨💠🗒🔲🐊 🐊');
    }

    #[DataProvider('providesStripTestByStringCases')]
    #[Test]
    public function strip_removes_expected_characters(string $string, string $search, string $expected): void
    {
        self::assertSame($expected, Str::strip($string, $search));
    }

    public static function providesStripTestByStringCases(): Generator
    {
        yield ['', '', ''];
        yield ['a', 'a', ''];
        yield ['Hello, World', '', 'Hello, World'];
        yield ['Hello, World', 'a', 'Hello, World'];
        yield ['Hello, World', 'l', 'Heo, Word'];
        yield ['Hello, World', 'Hello, World', ''];
        yield ['a⏬🎨💠🗒🔲🐊', 'a⏬🎨💠🗒🔲🐊', ''];
        yield ['a⏬🎨💠🗒🔲🐊', 'a', '⏬🎨💠🗒🔲🐊'];
        yield ['a⏬🎨💠🗒🔲🐊', '⏬🎨💠🗒🔲🐊', 'a'];
        yield ['👻😃👻', '👻', '😃'];
        yield ['👻😃👻', '😃', '👻👻'];
        yield [' The Quick Brown Fox Jumped Over The Lazy Dog. ', ' ', 'TheQuickBrownFoxJumpedOverTheLazyDog.'];
        yield [<<<'TAG'
            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed 
            do eiusmod tempor incididunt ut labore et dolore magna aliqua. 
            Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris 
            nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in 
            reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla 
            pariatur. Excepteur sint occaecat cupidatat non proident, sunt in 
            culpa qui officia deserunt mollit anim id est laborum.
            TAG, \PHP_EOL, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',];
    }

    #[DataProvider('providesStripTestCasesByRegExp')]
    #[Test]
    public function strip_removes_expected_characters_by_regexp(string $string, RegExp $regexp, string $expected): void
    {
        self::assertSame($expected, Str::strip($string, $regexp));
    }

    public static function providesStripTestCasesByRegExp(): Generator
    {
        yield ['', RegExp::make(''), ''];
        yield ['a', RegExp::make('a'), ''];
        yield ['aa', RegExp::make('a'), ''];
        yield ['Aa', RegExp::make('a'), 'A'];
        yield ['Aa', RegExp::make('a', 'i'), ''];
    }

    #[DataProvider('providesShortnameTestCases')]
    #[Test]
    public function shortname_returns_class_name_without_namespace(string $expected, string $classname): void
    {
        self::assertSame($expected, Str::shortname($classname));
    }

    public static function providesShortnameTestCases(): Generator
    {
        $expected = 'ShinyThing';
        yield 'fully-qualified' => [$expected, '\\' . ShinyThing::class];
        yield 'qualified' => [$expected, ShinyThing::class];
        yield 'relative' => [$expected, 'namespace\Entity\DailyMetrics\ShinyThing'];
        yield 'shortname' => [$expected, 'ShinyThing'];
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function snake_coverts_string_to_snake_case(array $expected, string $input): void
    {
        self::assertSame($expected['snake'], Str::snake($input));
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function kabob_coverts_string_to_kabob_case(array $expected, string $input): void
    {
        self::assertSame($expected['kabob'], Str::kabob($input));
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function pascal_coverts_string_to_pascal_case(array $expected, string $input): void
    {
        self::assertSame($expected['pascal'], Str::pascal($input));
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function camel_coverts_string_to_camel_case(array $expected, string $input): void
    {
        self::assertSame($expected['camel'], Str::camel($input));
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function screaming_coverts_string_to_screaming_snake_case(array $expected, string $input): void
    {
        self::assertSame($expected['screaming'], Str::screaming($input));
    }

    #[DataProvider('providesStringCaseConversionTestCases')]
    #[Test]
    public function dot_coverts_string_to_dot_case(array $expected, string $input): void
    {
        self::assertSame($expected['dot'], Str::dot($input));
    }

    public static function providesStringCaseConversionTestCases(): Generator
    {
        $expected = [
            'snake' => 'foo',
            'screaming' => 'FOO',
            'kabob' => 'foo',
            'pascal' => 'Foo',
            'camel' => 'foo',
            'dot' => 'foo',
        ];
        foreach ($expected as $input) {
            yield [$expected, $input];
        }

        $expected = [
            'snake' => 'the_quick_brown_fox_jumped_over_the_lazy_dog',
            'screaming' => 'THE_QUICK_BROWN_FOX_JUMPED_OVER_THE_LAZY_DOG',
            'kabob' => 'the-quick-brown-fox-jumped-over-the-lazy-dog',
            'pascal' => 'TheQuickBrownFoxJumpedOverTheLazyDog',
            'camel' => 'theQuickBrownFoxJumpedOverTheLazyDog',
            'dot' => 'the.quick.brown.fox.jumped.over.the.lazy.dog',
        ];

        foreach ($expected as $input) {
            yield [$expected, $input];
        }
        yield [$expected, 'The Quick Brown Fox Jumped Over The Lazy Dog'];
        yield [$expected, ' The    Quick   Brown   Fox    Jumped   Over   The   Lazy   Dog   '];
        yield [$expected, 'TheQuickBrownFoxJUMPEDOverTheLazyDog'];
        yield [$expected, ' TheQuickBrownFoxJUMPEDOverTheLazyDog '];
        yield [$expected, ' TheQuickBrownFoxJUMPEDOverTheLazyDog. '];
        yield [$expected, ' TheQUICKBrownFoxJUMPEDOverTheLazyDog. '];
        yield [$expected, ' theQUICKBrownFoxJUMPEDOverTheLazyDog. '];
        yield [$expected, ' TheQuickBrown_FoxJUMPEDOverThe_LazyDog. '];

        $expected = [
            'snake' => 'thequickbrownfoxjumpedoverthelazydog',
            'screaming' => 'THEQUICKBROWNFOXJUMPEDOVERTHELAZYDOG',
            'kabob' => 'thequickbrownfoxjumpedoverthelazydog',
            'pascal' => 'Thequickbrownfoxjumpedoverthelazydog',
            'camel' => 'thequickbrownfoxjumpedoverthelazydog',
            'dot' => 'thequickbrownfoxjumpedoverthelazydog',
        ];

        yield [$expected, 'THEQUICKBROWNFOXJUMPEDOVERTHELAZYDOG'];
        yield [$expected, 'thequickbrownfoxjumpedoverthelazydog'];

        $expected = [
            'snake' => 'some4_numbers234',
            'screaming' => 'SOME4_NUMBERS234',
            'kabob' => 'some4-numbers234',
            'pascal' => 'Some4Numbers234',
            'camel' => 'some4Numbers234',
            'dot' => 'some4.numbers234',
        ];

        yield [$expected, 'Some4Numbers234'];

        $expected = [
            'snake' => 'some_4_numbers_234',
            'screaming' => 'SOME_4_NUMBERS_234',
            'kabob' => 'some-4-numbers-234',
            'pascal' => 'Some4Numbers234',
            'camel' => 'some4Numbers234',
            'dot' => 'some.4.numbers.234',
        ];

        yield [$expected, 'Some 4 Numbers 234'];

        $expected = [
            'snake' => 'simple_xml',
            'screaming' => 'SIMPLE_XML',
            'kabob' => 'simple-xml',
            'pascal' => 'SimpleXml',
            'camel' => 'simpleXml',
            'dot' => 'simple.xml',
        ];

        yield [$expected, 'simpleXML'];
    }

    #[DataProvider('providesValidStringTestCases')]
    #[Test]
    public function object_returns_Stringable_of_string(string $expected, mixed $input): void
    {
        $object = Str::object($input);

        self::assertInstanceOf(\Stringable::class, $object);
        self::assertSame($expected, (string)$object);
    }

    #[DataProvider('providesTruncateTestCases')]
    #[Test]
    public function truncate_returns_expected_string(
        string|\Stringable $input,
        int $max_length,
        string $append,
        string $expected,
    ): void {
        self::assertSame($expected, Str::truncate($input, $max_length, $append));
    }

    public static function providesTruncateTestCases(): Generator
    {
        yield ['', 10, '', ''];
        yield ['', 10, '...', ''];
        yield ['Hello, world!', 13, '', 'Hello, world!'];
        yield ['Hello, world!', 13, '...', 'Hello, world!'];
        yield ['Hello, world!', 10, '', 'Hello, wor'];
        yield ['Hello, world!', 10, '...', 'Hello, ...'];
        yield ['Hello, world!', 3, '...', '...'];
        yield ['Hello, world!', 0, '', ''];
        yield [Str::object(''), 10, '', ''];
        yield [Str::object(''), 10, '...', ''];
        yield [Str::object('Hello, world!'), 13, '', 'Hello, world!'];
        yield [Str::object('Hello, world!'), 13, '...', 'Hello, world!'];
        yield [Str::object('Hello, world!'), 10, '', 'Hello, wor'];
        yield [Str::object('Hello, world!'), 10, '...', 'Hello, ...'];
        yield [Str::object('Hello, world!'), 3, '...', '...'];
        yield [Str::object('Hello, world!'), 0, '', ''];
    }

    #[Test]
    public function truncate_enforces_nonnegative_max_length(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Max Length Must Be Non-Negative');
        Str::truncate('Hello, world!', -1);
    }

    #[Test]
    public function truncate_enforces_max_append_length(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Trim Marker Length Must Be Less Than or Equal to Max Length');
        Str::truncate('Hello, world!', 3, '....');
    }
}
