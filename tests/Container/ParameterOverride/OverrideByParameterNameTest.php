<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ParameterOverride;

use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterName;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideByParameterNameTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $override = new OverrideByParameterName('foo', 'bar');
        self::assertSame('foo', $override->identifier());
        self::assertSame('bar', $override->value());
        self::assertSame(OverrideType::Name, $override->type());

        $override = new OverrideByParameterName('other');
        self::assertSame('other', $override->identifier());
        self::assertNull($override->value());
        self::assertSame(OverrideType::Name, $override->type());
    }

    #[Test]
    public function identifier_must_be_nonempty(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        new OverrideByParameterName('', 'bar');
    }
}
