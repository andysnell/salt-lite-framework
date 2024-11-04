<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Cast;

use PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture\IntBackedEnum;
use PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture\Stoplight;
use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NullableCast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NullableCastTest extends TestCase
{
    #[DataProvider('providesIntegerTestCases')]
    #[Test]
    public function integer_returns_expected_value(mixed $input, int|null $expected): void
    {
        self::assertSame($expected, NullableCast::integer($input));
    }

    public static function providesIntegerTestCases(): \Generator
    {
        yield [0, 0];
        yield [1, 1];
        yield [-1, -1];
        yield [1.4433, 1];
        yield [\PHP_INT_MAX, \PHP_INT_MAX];
        yield ['432', 432];
        yield ["hello, world", 0];
        yield ['0', 0];
        yield [true, 1];
        yield [false, 0];
        yield [null, null];
    }

    #[DataProvider('providesFloatTestCases')]
    #[Test]
    public function float_returns_expected_value(mixed $input, float|null $expected): void
    {
        self::assertSame($expected, NullableCast::float($input));
    }

    public static function providesFloatTestCases(): \Generator
    {
        yield [0, 0.0];
        yield [1, 1.0];
        yield [-1, -1.0];
        yield [1.4433, 1.4433];
        yield [\PHP_INT_MAX, (float)\PHP_INT_MAX];
        yield ['432', 432.0];
        yield ["hello, world", 0.0];
        yield ['0', 0.0];
        yield [true, 1.0];
        yield [false, 0.0];
        yield [null, null];
        yield [IntBackedEnum::Bar, 2.0];
        yield [Stoplight::Red, 0.0];
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function string_returns_expected_value(mixed $input, string|null $expected): void
    {
        self::assertSame($expected, NullableCast::string($input));
    }

    public static function providesStringTestCases(): \Generator
    {
        yield [0, '0'];
        yield [1, '1'];
        yield [-1, '-1'];
        yield [1.4433, '1.4433'];
        yield [\PHP_INT_MAX, (string)\PHP_INT_MAX];
        yield ['432', '432'];
        yield ["hello, world", "hello, world"];
        yield ['0', '0'];
        yield [true, '1'];
        yield [false, ''];
        yield [null, null];
        yield [IntBackedEnum::Bar, '2'];
        yield [Stoplight::Red, 'red'];
    }

    #[DataProvider('providesBooleanTestCases')]
    #[Test]
    public function boolean_returns_expected_value(mixed $input, bool|null $expected): void
    {
        self::assertSame($expected, NullableCast::boolean($input));
    }

    public static function providesBooleanTestCases(): \Generator
    {
        yield [0, false];
        yield [1, true];
        yield [-1, true];
        yield [1.4433, true];
        yield [\PHP_INT_MAX, true];
        yield ['432', true];
        yield ["hello, world", true];
        yield ['0', false];
        yield [true, true];
        yield [false, false];
        yield [null, null];
        yield [IntBackedEnum::Bar, true];
        yield [Stoplight::Red, true];
    }
}
