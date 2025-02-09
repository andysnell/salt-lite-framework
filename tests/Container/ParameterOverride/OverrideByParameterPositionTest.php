<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ParameterOverride;

use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterPosition;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideByParameterPositionTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $override = new OverrideByParameterPosition(2, 'bar');
        self::assertSame(2, $override->identifier());
        self::assertSame('bar', $override->value());
        self::assertSame(OverrideType::Position, $override->type());

        $override = new OverrideByParameterPosition(0);
        self::assertSame(0, $override->identifier());
        self::assertNull($override->value());
        self::assertSame(OverrideType::Position, $override->type());
    }

    #[Test]
    public function identifier_must_be_nonempty(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new OverrideByParameterPosition(-1, 'bar');
    }
}
