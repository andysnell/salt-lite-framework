<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Cast;

use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmptyOrNull;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NonEmptyOrNullTest extends TestCase
{
    #[DataProvider('providesIntegerTestCases')]
    #[Test]
    public function integer_returns_expected_value(mixed $input, int|null $expected): void
    {
        self::assertSame($expected, NonEmptyOrNull::integer($input));
    }

    public static function providesIntegerTestCases(): \Generator
    {
        yield [0, null];
        yield [1, 1];
        yield [-1, -1];
        yield [1.4433, 1];
        yield [\PHP_INT_MAX, \PHP_INT_MAX];
        yield [\PHP_INT_MIN, \PHP_INT_MIN];
        yield ['432', 432];
        yield ["hello, world", null];
        yield ['0', null];
        yield ['0.0', null];
        yield [true, 1];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesFloatTestCases')]
    #[Test]
    public function float_returns_expected_value(mixed $input, float|null $expected): void
    {
        self::assertSame($expected, NonEmptyOrNull::float($input));
    }

    public static function providesFloatTestCases(): \Generator
    {
        yield [0, null];
        yield [0.0, null];
        yield [1, 1.0];
        yield [-1, -1.0];
        yield [1.4433, 1.4433];
        yield [\PHP_INT_MAX, (float)\PHP_INT_MAX];
        yield [\PHP_INT_MIN, (float)\PHP_INT_MIN];
        yield ['432', 432.0];
        yield ["hello, world", null];
        yield ['0', null];
        yield ['0.0', null];
        yield [true, 1.0];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function string_returns_expected_value(mixed $input, string|null $expected): void
    {
        self::assertSame($expected, NonEmptyOrNull::string($input));
    }

    public static function providesStringTestCases(): \Generator
    {
        yield [0, null];
        yield [0.0, null];
        yield [1, '1'];
        yield [-1, '-1'];
        yield [1.4433, '1.4433'];
        yield [\PHP_INT_MAX, (string)\PHP_INT_MAX];
        yield [\PHP_INT_MIN, (string)\PHP_INT_MIN];
        yield ['432', '432'];
        yield ["hello, world", "hello, world"];
        yield ['0', null];
        yield ['0.0', '0.0'];
        yield [true, '1'];
        yield [false, null];
        yield [null, null];
    }

    #[DataProvider('providesBooleanTestCases')]
    #[Test]
    public function boolean_returns_expected_value(mixed $input, bool|null $expected): void
    {
        self::assertSame($expected, NonEmptyOrNull::boolean($input));
    }

    public static function providesBooleanTestCases(): \Generator
    {
        yield [0, null];
        yield [1, true];
        yield [-1, true];
        yield [1.4433, true];
        yield [\PHP_INT_MAX, true];
        yield [\PHP_INT_MIN, true];
        yield ['432', true];
        yield ["hello, world", true];
        yield ['0', null];
        yield ['0.0', true];
        yield [true, true];
        yield [false, null];
        yield [null, null];
        yield [[], null];
        yield [[1,2,3], true];
        yield [['foo' => 'bar'], true];
        yield [new \stdClass(), true];
    }

    #[DataProvider('providesArrayTestCases')]
    #[Test]
    public function array_returns_expected_value(mixed $input, array|null $expected): void
    {
        self::assertSame($expected, NonEmptyOrNull::array($input));
    }

    public static function providesArrayTestCases(): \Generator
    {
        yield [null, null];
        yield [[], null];
        yield [[1,2,3], [1,2,3]];
        yield [['foo' => 'bar'], ['foo' => 'bar']];
    }
}
