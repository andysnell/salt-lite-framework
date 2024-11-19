<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AreaCodeStatusTest extends TestCase
{
    #[DataProvider('providesTestCases')]
    #[Test]
    public function mask_applies_expected_bitmask(int $status, int $expected): void
    {
        self::assertSame($expected, AreaCodeStatus::mask($status));
    }

    /**
     * @return \Generator<array{int, int}>
     */
    public static function providesTestCases(): \Generator
    {
        yield [AreaCodeStatus::INVALID, AreaCodeStatus::INVALID];
        yield [AreaCodeStatus::ASSIGNABLE, AreaCodeStatus::ASSIGNABLE];
        yield [AreaCodeStatus::ASSIGNED, AreaCodeStatus::ASSIGNED];
        yield [AreaCodeStatus::SCHEDULED, AreaCodeStatus::SCHEDULED];
        yield [AreaCodeStatus::ACTIVE, AreaCodeStatus::ACTIVE];

        yield [0, AreaCodeStatus::INVALID];
        yield [0b1111, AreaCodeStatus::ACTIVE | AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED | AreaCodeStatus::SCHEDULED];
        yield [0b111111111111, AreaCodeStatus::ACTIVE | AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED | AreaCodeStatus::SCHEDULED];
        yield [0b000000001111, AreaCodeStatus::ACTIVE | AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED | AreaCodeStatus::SCHEDULED];
        yield [0b111111110111, AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED | AreaCodeStatus::SCHEDULED];
        yield [0b111111110011, AreaCodeStatus::ASSIGNABLE | AreaCodeStatus::ASSIGNED];
    }
}
