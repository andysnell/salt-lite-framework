<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Domain\Uuid\RandomUuid;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class RandomUuidTest extends TestCase
{
    #[Test]
    public function it_is_a_UUID(): void
    {
        $uuid = new RandomUuid();

        self::assertTrue(Uuid::isValid((string)$uuid));
        self::assertSame(Uuid::UUID_TYPE_RANDOM, $uuid->getFields()->getVersion());
        self::assertSame(Uuid::UUID_TYPE_RANDOM, Uuid::fromString((string)$uuid)->getFields()->getVersion());
    }
}
