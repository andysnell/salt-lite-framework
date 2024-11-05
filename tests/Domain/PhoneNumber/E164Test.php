<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\E164;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class E164Test extends TestCase
{
    #[DataProvider('providesValidTestCases')]
    #[Test]
    public function make_returns_instance_of_E164(string $test, string $expected): void
    {
        $e164 = E164::make($test);

        self::assertInstanceOf(E164::class, $e164);
        self::assertSame($expected, (string)$e164);
        self::assertSame(E164::make($e164), $e164);

        self::assertEquals($e164, E164::make(new class ($test) implements \Stringable {
            public function __construct(private readonly string $phone_number)
            {
            }

            public function __toString(): string
            {
                return $this->phone_number;
            }
        }));

        self::assertSame($e164, E164::make(new class ($e164) implements PhoneNumber {
            public function __construct(private readonly E164 $e164)
            {
            }

            public function toE164(): E164
            {
                return $this->e164;
            }
        }));

        self::assertSame($e164, $e164->toE164());
        self::assertSame($e164, $e164->getPhoneNumber());
    }

    #[DataProvider('providesInvalidTestCases')]
    #[Test]
    public function make_throws_exception_for_invalid(string $test): void
    {
        $this->expectException(InvalidPhoneNumber::class);
        E164::make($test);
    }

    #[DataProvider('providesValidTestCases')]
    #[Test]
    public function tryFrom_returns_instance_of_E164(string $test, string $expected): void
    {
        $e164 = E164::tryFrom($test);

        self::assertInstanceOf(E164::class, $e164);
        self::assertSame($expected, (string)$e164);
        self::assertSame(E164::tryFrom($e164), $e164);

        self::assertEquals($e164, E164::tryFrom(new class ($test) implements \Stringable {
            public function __construct(private readonly string $phone_number)
            {
            }

            public function __toString(): string
            {
                return $this->phone_number;
            }
        }));

        self::assertSame($e164, E164::tryFrom(new class ($e164) implements PhoneNumber {
            public function __construct(private readonly E164 $e164)
            {
            }

            public function toE164(): E164
            {
                return $this->e164;
            }
        }));

        self::assertSame($e164, $e164->toE164());
    }

    #[DataProvider('providesInvalidTestCases')]
    #[Test]
    public function tryFrom_returns_null_for_invalid(string $test): void
    {
        self::assertNull(E164::tryFrom($test));
    }

    #[DataProvider('providesValidTestCases')]
    #[Test]
    public function it_can_be_serialized_and_deserialized(string $test, string $expected): void
    {
        $e164 = E164::make($test);

        $serialized = \serialize($e164);
        $deserialized = \unserialize($serialized, ['allowed_classes' => [E164::class]]);

        self::assertInstanceOf(E164::class, $deserialized);
        self::assertEquals($e164, $deserialized);
        self::assertSame($expected, (string)$deserialized);
    }

    #[DataProvider('providesValidTestCases')]
    #[Test]
    public function jsonSerialize_returns_expected_string(string $test, string $expected): void
    {
        self::assertSame($expected, E164::make($test)->jsonSerialize());
    }

    public static function providesValidTestCases(): \Generator
    {
        yield ['+13145551234', '+13145551234'];
        yield ['3145551234', '+13145551234'];
        yield ['13145551234', '+13145551234'];
        yield ['1-314-555-1234', '+13145551234'];
        yield ["(314)-555-1234", '+13145551234'];
        yield ["+44 117 496 0123", '+441174960123'];
        yield ["441174960123", '+441174960123'];
    }

    public static function providesInvalidTestCases(): \Generator
    {
        yield 'string' => ['Hello, World'];
        yield 'empty string' => [''];
        yield 'too short' => ['123456'];
    }
}
