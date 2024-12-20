<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Domain\Uuid\RandomUuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Uuid;

final class RandomUuidTest extends TestCase
{
    #[Test]
    public function it_is_a_UUID(): void
    {
        $uuid = new RandomUuid();

        self::assertTrue(Uuid::isValid((string)$uuid));

        $fields = $uuid->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_RANDOM, $fields->getVersion());

        $fields = Uuid::fromString((string)$uuid)->getFields();
        self::assertInstanceOf(FieldsInterface::class, $fields);
        self::assertSame(Uuid::UUID_TYPE_RANDOM, $fields->getVersion());
    }
}
