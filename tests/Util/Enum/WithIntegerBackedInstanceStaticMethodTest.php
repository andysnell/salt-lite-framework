<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Enum;

use PhoneBurner\SaltLite\Framework\Tests\Fixtures\ArabicNumerals;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WithIntegerBackedInstanceStaticMethodTest extends TestCase
{
    #[Test]
    public function instance_returns_expected_instance(): void
    {
        foreach (ArabicNumerals::cases() as $case) {
            self::assertSame($case, ArabicNumerals::instance($case));
            self::assertSame($case, ArabicNumerals::instance($case->value));
            self::assertSame($case, ArabicNumerals::instance((string)$case->value));
        }
    }

    #[Test]
    public function instance_throws_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ArabicNumerals::instance('invalid');
    }
}
