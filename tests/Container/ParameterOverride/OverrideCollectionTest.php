<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ParameterOverride;

use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterName;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterPosition;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideByParameterType;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideCollection;
use PhoneBurner\SaltLite\Framework\Container\ParameterOverride\OverrideType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OverrideCollectionTest extends TestCase
{
    #[Test]
    public function empty_collection_tests(): void
    {
        $collection = new OverrideCollection();
        self::assertFalse($collection->has(OverrideType::Position, 2));
        self::assertFalse($collection->has(OverrideType::Name, 'bar'));
        self::assertFalse($collection->has(OverrideType::Hint, 'SomeClassName'));
        self::assertNull($collection->find(OverrideType::Position, 2));
        self::assertNull($collection->find(OverrideType::Name, 'bar'));
        self::assertNull($collection->find(OverrideType::Hint, 'SomeClassName'));
    }

    #[Test]
    public function happy_path_tests(): void
    {
        $type_override = new OverrideByParameterType('SomeOtherClassName', 'bar');
        $name_override = new OverrideByParameterName('baz', 'bar');
        $position_override = new OverrideByParameterPosition(3, 'bar');
        $collection = new OverrideCollection(
            $type_override,
            $name_override,
            $position_override,
        );

        self::assertTrue($collection->has(OverrideType::Position, 3));
        self::assertTrue($collection->has(OverrideType::Name, 'baz'));
        self::assertTrue($collection->has(OverrideType::Hint, 'SomeOtherClassName'));
        self::assertSame($type_override, $collection->find(OverrideType::Hint, 'SomeOtherClassName'));
        self::assertSame($name_override, $collection->find(OverrideType::Name, 'baz'));
        self::assertSame($position_override, $collection->find(OverrideType::Position, 3));

        self::assertFalse($collection->has(OverrideType::Position, 2));
        self::assertFalse($collection->has(OverrideType::Name, 'bar'));
        self::assertFalse($collection->has(OverrideType::Hint, 'SomeClassName'));
        self::assertNull($collection->find(OverrideType::Position, 2));
        self::assertNull($collection->find(OverrideType::Name, 'bar'));
        self::assertNull($collection->find(OverrideType::Hint, 'SomeClassName'));
    }
}
