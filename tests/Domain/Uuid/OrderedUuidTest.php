<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Domain\Uuid\OrderedUuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class OrderedUuidTest extends TestCase
{
    #[Test]
    public function it_is_a_UUID(): void
    {
        $uuid = new OrderedUuid();

        self::assertTrue(Uuid::isValid((string)$uuid));
        self::assertSame(Uuid::UUID_TYPE_UNIX_TIME, $uuid->getFields()->getVersion());
        self::assertSame(Uuid::UUID_TYPE_UNIX_TIME, Uuid::fromString((string)$uuid)->getFields()->getVersion());
    }
}
