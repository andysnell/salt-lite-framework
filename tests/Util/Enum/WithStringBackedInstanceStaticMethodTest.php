<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Enum;

use PhoneBurner\SaltLite\Framework\Tests\Fixtures\StoplightState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WithStringBackedInstanceStaticMethodTest extends TestCase
{
    #[Test]
    public function instance_returns_expected_instance(): void
    {
        foreach (StoplightState::cases() as $case) {
            self::assertSame($case, StoplightState::instance($case));
            self::assertSame($case, StoplightState::instance($case->value));
            self::assertSame($case, StoplightState::instance(\strtoupper($case->value)));
            self::assertSame($case, StoplightState::instance(\strtolower($case->value)));
        }
    }

    #[Test]
    public function instance_throws_invalid_argument_exception_on_bad_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StoplightState::instance('invalid');
    }

    #[Test]
    public function instance_throws_invalid_argument_exception_on_bad_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StoplightState::instance(new \stdClass());
    }
}
