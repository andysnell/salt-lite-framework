<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Domain\Uuid\UuidString;
use PhoneBurner\SaltLite\Framework\Domain\Uuid\UuidWrapper;
use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(UuidWrapper::class)]
class UuidStringTest extends TestCase
{
    #[Test]
    public function it_is_a_UUID(): void
    {
        $uuid = Uuid::ordered();
        $wrapped_uuid = new UuidString($uuid);

        self::assertTrue($wrapped_uuid->equals($uuid));
        self::assertSame(0, $wrapped_uuid->compareTo($uuid));
        self::assertSame(0, $uuid->compareTo($wrapped_uuid));
        self::assertTrue($wrapped_uuid->equals($uuid));
        self::assertTrue($wrapped_uuid->equals($wrapped_uuid));
        self::assertTrue($uuid->equals($wrapped_uuid));
        self::assertSame($uuid->getBytes(), $wrapped_uuid->getBytes());
        self::assertSame($uuid->getFields(), $wrapped_uuid->getFields());
        self::assertEquals($uuid->getHex(), $wrapped_uuid->getHex());
        self::assertEquals($uuid->getInteger(), $wrapped_uuid->getInteger());
        self::assertSame($uuid->toString(), $wrapped_uuid->toString());
        self::assertSame((string)$uuid, (string)$wrapped_uuid);

        $deserialized = \unserialize(\serialize($wrapped_uuid));
        self::assertSame($wrapped_uuid->toString(), $deserialized->toString());
        self::assertTrue($deserialized->equals($uuid));
        self::assertSame(0, $deserialized->compareTo($uuid));
        self::assertSame(0, $uuid->compareTo($deserialized));
        self::assertTrue($deserialized->equals($uuid));
        self::assertTrue($deserialized->equals($wrapped_uuid));
        self::assertTrue($uuid->equals($deserialized));
        self::assertSame($uuid->getBytes(), $deserialized->getBytes());
        self::assertEquals($uuid->getFields(), $deserialized->getFields());
        self::assertEquals($uuid->getHex(), $deserialized->getHex());
        self::assertEquals($uuid->getInteger(), $deserialized->getInteger());
        self::assertSame($uuid->toString(), $deserialized->toString());
        self::assertSame((string)$uuid, (string)$deserialized);
    }
}
