<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ParameterOverride;

use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterType;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideByParameterTypeTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $override = new OverrideByParameterType('SomeClassName', 'bar');
        self::assertSame('SomeClassName', $override->identifier());
        self::assertSame('bar', $override->value());
        self::assertSame(OverrideType::Hint, $override->type());

        $override = new OverrideByParameterType('SomeOtherClassName');
        self::assertSame('SomeOtherClassName', $override->identifier());
        self::assertNull($override->value());
        self::assertSame(OverrideType::Hint, $override->type());
    }

    #[Test]
    public function identifier_must_be_nonempty(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new OverrideByParameterType('', 'bar');
    }
}
