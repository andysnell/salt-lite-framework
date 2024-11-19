<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Cast;

use PhoneBurner\SaltLite\Framework\Util\Helper\Cast\NonEmpty;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NonEmptyTest extends TestCase
{
    #[Test]
    public function empty_string_throws_default_exception(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('String Must Not Be Empty');
        NonEmpty::string('');
    }

    #[Test]
    public function empty_string_throws_exception(): void
    {
        $exception = new \RuntimeException('Custom Exception');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Custom Exception');
        NonEmpty::string('', $exception);
    }

    #[DataProvider('providesStringTestCases')]
    #[Test]
    public function string_returns_expected_value(string $input): void
    {
        self::assertSame($input, NonEmpty::string($input));
    }

    public static function providesStringTestCases(): \Generator
    {
        yield ['432',];
        yield ["hello, world"];
        yield ['0'];
        yield ['0.0'];
    }
}
