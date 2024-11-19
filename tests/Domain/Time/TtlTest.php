<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Time;

use PhoneBurner\SaltLite\Framework\Domain\Time\TimeConstant;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TtlTest extends TestCase
{
    #[Test]
    public function sut_can_be_instantiated_with_defaults(): void
    {
        self::assertSame(Ttl::DEFAULT_SECONDS, (new Ttl())->seconds);
        self::assertSame(Ttl::DEFAULT_SECONDS, Ttl::seconds()->seconds);
        self::assertSame(Ttl::DEFAULT_SECONDS, Ttl::minutes()->seconds);
        self::assertSame(TimeConstant::SECONDS_IN_HOUR, Ttl::hours()->seconds);
        self::assertSame(TimeConstant::SECONDS_IN_DAY, Ttl::days()->seconds);
    }

    #[Test]
    public function sut_can_be_instantiated_with_max_ttl(): void
    {
        self::assertSame(\PHP_INT_MAX, Ttl::max()->seconds);
    }

    #[Test]
    public function sut_can_be_instantiated_with_min_ttl(): void
    {
        self::assertSame(0, Ttl::min()->seconds);
    }

    #[DataProvider('provideSeconds')]
    #[Test]
    public function sut_can_be_instantiated_with_seconds(int|float $expected, int|float $seconds): void
    {
        self::assertSame($expected, (new Ttl($seconds))->seconds);
        self::assertSame($expected, Ttl::seconds($seconds)->seconds);
    }

    public static function provideSeconds(): \Generator
    {
        yield [0, 0];
        yield [0.0, 0.0];
        yield [3 * TimeConstant::SECONDS_IN_MINUTE, 3 * TimeConstant::SECONDS_IN_MINUTE];
        yield [0.1234, 0.1234];
        yield [\PHP_INT_MAX, \PHP_INT_MAX];
        yield [\PHP_INT_MAX + 1, \PHP_INT_MAX + 1];
    }

    #[DataProvider('provideMinutes')]
    #[Test]
    public function sut_can_be_instantiated_with_minutes(int|float $expected, int|float $minutes): void
    {
        self::assertSame($expected, Ttl::minutes($minutes)->seconds);
    }

    public static function provideMinutes(): \Generator
    {
        yield [0, 0];
        yield [0.0, 0.0];
        yield [5 * TimeConstant::SECONDS_IN_MINUTE, 5];
        yield [0.1234 * TimeConstant::SECONDS_IN_MINUTE, 0.1234];
        yield [\PHP_INT_MAX * TimeConstant::SECONDS_IN_MINUTE, \PHP_INT_MAX];
    }

    #[DataProvider('provideHours')]
    #[Test]
    public function sut_can_be_instantiated_with_hours(int $expected, int $hours): void
    {
        self::assertSame($expected, Ttl::hours($hours)->seconds);
    }

    public static function provideHours(): \Generator
    {
        yield [0, 0];
        yield [TimeConstant::SECONDS_IN_HOUR, 1];
        yield [5 * TimeConstant::SECONDS_IN_HOUR, 5];
        yield [24 * TimeConstant::SECONDS_IN_HOUR, 24];
    }

    #[DataProvider('provideDays')]
    #[Test]
    public function sut_can_be_instantiated_with_days(int $expected, int $days): void
    {
        self::assertSame($expected, Ttl::days($days)->seconds);
    }

    public static function provideDays(): \Generator
    {
        yield [0, 0];
        yield [TimeConstant::SECONDS_IN_DAY, 1];
        yield [7 * TimeConstant::SECONDS_IN_DAY, 7];
        yield [31 * TimeConstant::SECONDS_IN_DAY, 31];
    }

    #[DataProvider('provideDateTimeInterfaces')]
    #[Test]
    public function sut_can_be_instantiated_based_on_datetime(
        int|float $expected,
        \DateTimeInterface $datetime,
        \DateTimeInterface $now,
    ): void {
        self::assertSame($expected, Ttl::until($datetime, $now)->seconds);
    }

    public static function provideDateTimeInterfaces(): \Generator
    {
        $now = new \DateTimeImmutable();
        yield [0, $now, $now];
        yield [3600, $now->add(new \DateInterval('PT1H')), $now];
        yield [3782, $now->add(new \DateInterval('PT1H3M2S')), $now];
        yield [0, new \DateTimeImmutable('@0'), new \DateTimeImmutable('@0')];
    }

    #[DataProvider('provideInvalidSeconds')]
    #[Test]
    public function time_to_live_cannot_be_negative(int|float $seconds): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new Ttl($seconds);
    }

    public static function provideInvalidSeconds(): \Generator
    {
        yield [-1];
        yield [-0.1];
        yield [\PHP_INT_MIN];
        yield [(float)\PHP_INT_MIN];
    }

    #[DataProvider('provideValidMakeTestCases')]
    #[Test]
    public function make_returns_expected_ttl(mixed $input, Ttl $expected, \DateTimeImmutable|null $now = null): void
    {
        self::assertEquals($expected, Ttl::make($input, $now ?? new \DateTimeImmutable()));
    }

    public static function provideValidMakeTestCases(): \Generator
    {
        $datetime = new \DateTimeImmutable();

        yield [Ttl::seconds(42), Ttl::seconds(42)];
        yield [new \DateInterval('PT1H'), Ttl::seconds(3600)];
        yield [new \DateInterval('PT1H42S'), Ttl::seconds(3642)];
        yield [new \DateTimeImmutable('@0'), Ttl::seconds(0), new \DateTimeImmutable('@0')];
        yield [$datetime->add(new \DateInterval('PT1H42S')), Ttl::seconds(3642), $datetime];
        yield [null, Ttl::max()];
        yield [0, Ttl::min()];
        yield [0.0, Ttl::min()];
        yield [0.1234, Ttl::seconds(0.1234)];
        yield [1, Ttl::seconds(1)];
        yield [12345, Ttl::seconds(12345)];
        yield [12345.67, Ttl::seconds(12345.67)];
        yield ['0', Ttl::min()];
        yield ['0.0', Ttl::min()];
        yield ['0.1234', Ttl::seconds(0.1234)];
        yield ['1', Ttl::seconds(1)];
        yield ['12345', Ttl::seconds(12345)];
        yield ['12345.67', Ttl::seconds(12345.67)];
    }

    #[DataProvider('provideInvalidMakeTestCases')]
    #[Test]
    public function make_throws_exception_with_invalid_input(mixed $input): void
    {
        $this->expectException(\UnexpectedValueException::class);
        Ttl::make($input);
    }

    public static function provideInvalidMakeTestCases(): \Generator
    {
        yield [-1];
        yield [-0.1];
        yield [\PHP_INT_MIN];
        yield [(float)\PHP_INT_MIN];
        yield ['foo'];
        yield [['foo' => 'bar']];
        yield [[]];
        yield [new \stdClass()];
    }
}
