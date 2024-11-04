<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use Generator;
use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Rfc4122\NilUuid;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use stdClass;
use Stringable;
use Throwable;

class UuidTest extends TestCase
{
    #[Test]
    public function random_returns_version4_uuid_instances(): void
    {
        $uuid = Uuid::ordered();
        $reduced_comparison = 0;
        for ($i = 0; $i < 100; ++$i) {
            $new_uuid = Uuid::random();
            self::assertInstanceOf(UuidV4::class, $new_uuid);
            self::assertMatchesRegularExpression(Uuid::HEX_REGEX, (string)$new_uuid);
            $fields = $uuid->getFields();
            self::assertInstanceOf(FieldsInterface::class, $fields);
            self::assertSame(2, $fields->getVariant());
            self::assertSame(4, $fields->getVersion());
            $reduced_comparison += $new_uuid->compareTo($uuid);
            $uuid = $new_uuid;
        }

        self::assertNotSame(100, $reduced_comparison);
    }

    #[Test]
    public function nil_returns_the_nil_uuid_instance(): void
    {
        $uuid = Uuid::nil();
        self::assertInstanceOf(NilUuid::class, $uuid);
        self::assertSame('00000000-0000-0000-0000-000000000000', $uuid->toString());
        self::assertSame("\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0", $uuid->getBytes());
        $fields = $uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertTrue($fields->isNil());
        self::assertNull($fields->getVersion());
        self::assertSame($uuid, Uuid::nil());
    }

    #[Test]
    public function ordered_returns_timestamp_first_comb_uuid_instances(): void
    {
        $uuid = Uuid::ordered();
        $reduced_comparison = 0;
        for ($i = 0; $i < 100; ++$i) {
            $new_uuid = Uuid::ordered();
            self::assertMatchesRegularExpression(Uuid::HEX_REGEX, (string)$new_uuid);
            self::assertLessThan($new_uuid->toString(), $uuid->toString());
            self::assertLessThan($new_uuid->getBytes(), $uuid->getBytes());
            $fields = $uuid->getFields();
            self::assertInstanceOf(FieldsInterface::class, $fields);
            self::assertSame(2, $fields->getVariant());
            self::assertSame(4, $fields->getVersion());
            $reduced_comparison += $new_uuid->compareTo($uuid);
            $uuid = $new_uuid;
        }

        self::assertSame(100, $reduced_comparison);
    }

    public function fromString_returns_matching_uuid(): void
    {
        $uuid = Uuid::random();
        self::assertTrue($uuid->equals(
            Uuid::instance($uuid->toString()),
        ));
    }

    #[Test]
    public function getOrderedFactory_returns_factory_with_caching(): void
    {
        $factory = Uuid::getOrderedFactory();
        self::assertInstanceOf(UuidFactoryInterface::class, $factory);
        self::assertSame($factory, Uuid::getOrderedFactory());
        self::assertSame($factory, Uuid::getOrderedFactory());
    }

    #[Test]
    public function getRandomFactory_returns_factory_with_caching(): void
    {
        $factory = Uuid::getRandomFactory();
        self::assertInstanceOf(UuidFactoryInterface::class, $factory);
        self::assertSame($factory, Uuid::getRandomFactory());
        self::assertSame($factory, Uuid::getRandomFactory());
    }

    #[Test]
    public function instance_returns_same_UuidInterface_instance(): void
    {
        $uuid = Uuid::random();
        self::assertSame($uuid, Uuid::instance($uuid));
    }

    #[Test]
    public function instance_casts_strings_to_UuidInterface(): void
    {
        $uuid = Uuid::random();

        $uuid_upper_string = \strtoupper($uuid->toString());
        $uuid_lower_string = \strtolower($uuid->toString());
        $uuid_stringable = new readonly class ($uuid) implements Stringable {
            public function __construct(private UuidInterface $uuid)
            {
            }

            public function __toString(): string
            {
                return (string)$this->uuid;
            }
        };

        self::assertEquals($uuid, Uuid::instance($uuid_upper_string));
        self::assertEquals($uuid, Uuid::instance($uuid_lower_string));
        self::assertEquals($uuid, Uuid::instance($uuid_stringable));
    }

    #[DataProvider('provideUncastableUuidValues')]
    #[Test]
    public function instance_throws_exception_if_cannot_cast_to_UuidInterface(mixed $value): void
    {
        $this->expectException(Throwable::class);
        Uuid::instance($value);
    }

    public static function provideUncastableUuidValues(): Generator
    {
        yield [''];
        yield [new stdClass()];
        yield [1234567890];
        yield ['Z0000000-0000-0000-0000-000000000000'];
    }
}
