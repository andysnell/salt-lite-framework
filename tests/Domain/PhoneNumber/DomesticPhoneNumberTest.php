<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\E164;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\Exception\InvalidPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\PhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\PhoneNumberFormat;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DomesticPhoneNumberTest extends TestCase
{
    #[DataProvider('invalidPhoneDataProvider')]
    #[Test]
    public function constructor_validates_phone_numbers(string $test): void
    {
        $this->expectException(InvalidPhoneNumber::class);
        DomesticPhoneNumber::make($test);
    }

    #[Test]
    public function make_validates_phone_numbers(): void
    {
        $this->expectException(\TypeError::class);
        /* @phpstan-ignore-next-line intentional defect */
        DomesticPhoneNumber::make(new \stdClass());
    }

    #[DataProvider('invalidPhoneDataProvider')]
    #[Test]
    public function tryFrom_returns_null_on_invalid_validates_phone_numbers(string $test): void
    {
        self::assertNull(DomesticPhoneNumber::tryFrom($test));
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function tryFrom_returns_self(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertSame($phone_number, DomesticPhoneNumber::tryFrom($phone_number));
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function tryFrom_makes_valid_phone_numbers(string $test): void
    {
        self::assertEquals(DomesticPhoneNumber::make($test), DomesticPhoneNumber::tryFrom($test));
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function make_returns_self(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertSame($phone_number, DomesticPhoneNumber::make($phone_number));
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function make_casts_Stringable(string $test): void
    {
        $phone_number = DomesticPhoneNumber::make($test);
        self::assertEquals($phone_number, DomesticPhoneNumber::make(new class ($test) implements \Stringable {
            public function __construct(private readonly string $phone_number)
            {
            }

            public function __toString(): string
            {
                return $this->phone_number;
            }
        }));
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function make_reuses_valid_e164(string $test): void
    {
        $e164 = E164::make($test);
        self::assertSame($e164, DomesticPhoneNumber::make(new class ($e164) implements PhoneNumber {
            public function __construct(private readonly E164 $e164)
            {
            }

            public function toE164(): E164
            {
                return $this->e164;
            }
        })->toE164());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function jsonSerialize_returns_expected_string(string $test, string $expected): void
    {
        self::assertSame($expected, DomesticPhoneNumber::make($test)->jsonSerialize());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function it_can_be_serialized_and_deserialized(string $test, string $expected): void
    {
        $phone_number = DomesticPhoneNumber::make($test);

        $serialized = \serialize($phone_number);
        $deserialized = \unserialize($serialized, ['allowed_classes' => [DomesticPhoneNumber::class]]);

        self::assertInstanceOf(DomesticPhoneNumber::class, $deserialized);
        self::assertEquals($phone_number, $deserialized);
        self::assertSame($expected, (string)$deserialized->toE164());
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function normalized_returns_e164_formatted_phone_number(string $test, string $normalized): void
    {
        self::assertSame($normalized, DomesticPhoneNumber::make($test)->normalize());
    }

    #[DataProvider('formatDataProvider')]
    #[Test]
    public function format_returns_the_phone_number_expected_string_format(string $test, array $formats): void
    {
        self::assertSame(
            $formats['national'],
            DomesticPhoneNumber::make($test)->format(),
        );

        self::assertSame(
            $formats['national'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::National),
        );

        self::assertSame(
            $formats['strip_prefix'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::StripPrefix),
        );

        self::assertSame(
            $formats['e164'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::E164),
        );

        self::assertSame(
            $formats['international'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::International),
        );

        self::assertSame(
            $formats['rfc3966'],
            DomesticPhoneNumber::make($test)->format(PhoneNumberFormat::Rfc3966),
        );
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function toString_returns_the_unformatted_phone_number(string $test, string $normalized): void
    {
        $phone = DomesticPhoneNumber::make($test);
        self::assertSame(\substr($normalized, 2), (string)$phone);
    }

    #[DataProvider('provideAreaCodes')]
    #[Test]
    public function getAreaCode_parses_appropriate_string(string $test, int $expected): void
    {
        self::assertSame($expected, DomesticPhoneNumber::make($test)->getAreaCode()->npa);
    }

    #[DataProvider('e164DataProvider')]
    #[Test]
    public function toE164_returns_expected_phone_number(string $test, string $expected): void
    {
        $phone = DomesticPhoneNumber::make($test);

        self::assertSame($expected, (string)$phone->toE164());
    }

    /**
     * @return \Generator<array{string, int}>
     */
    public static function provideAreaCodes(): \Generator
    {
        yield ['3145551234', 314];
        yield ['13145551234', 314];
        yield ['1-314-555-1234', 314];
        yield ["3145551234\n", 314];
        yield ["13145551234\n", 314];
        yield ["(314) 555-1234", 314];
        yield ["(314)-555-1234", 314];
        yield ["(314)-555-1234 ext.", 314];
    }

    /**
     * @return \Generator<array{string, string}>
     */
    public static function e164DataProvider(): \Generator
    {
        yield from [
            ['3145551234', '+13145551234'],
            ['13145551234', '+13145551234'],
            ['1-314-555-1234', '+13145551234'],
            ["3145551234\n", '+13145551234'],
            ["13145551234\n", '+13145551234'],
            ["(314) 555-1234", '+13145551234'],
            ["(314)-555-1234", '+13145551234'],
            ["(314)-555-1234 ext.", '+13145551234'],
        ];
    }

    /**
     * @return \Generator<array{string, array<string,string>}>
     */
    public static function formatDataProvider(): \Generator
    {
        $formatted = [
            'national' => '(314) 555-1234',
            'strip_prefix' => '3145551234',
            'e164' => '+13145551234',
            'international' => '+1 314-555-1234',
            'rfc3966' => 'tel:+1-314-555-1234',
        ];

        yield from [
            ['3145551234', $formatted],
            ['13145551234', $formatted],
            ['1-314-555-1234 ', $formatted],
            ["3145551234\n", $formatted],
            ["13145551234\n", $formatted],
            ["(314) 555-1234 ", $formatted],
            ["(314)-555-1234", $formatted],
            ["(314)-555-1234 ext.", $formatted],
        ];
    }

    /**
     * @return \Generator<array{string}>
     */
    public static function invalidPhoneDataProvider(): \Generator
    {
        yield from [
            ['invalid'],
            ['0'],
            ['314AAA1234'],
            ['013145551234'],
            ['131455512345'],
            ['1234567890'],
            ['1234567890'],
            ['3140551234'],
            ['3140551234'],
        ];
    }
}
