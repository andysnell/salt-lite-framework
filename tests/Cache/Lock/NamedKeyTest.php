<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKey;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKeyFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NamedKeyTest extends TestCase
{
    #[Test]
    public function a_named_key_name_cannot_be_empty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The name cannot be empty.');
        new NamedKey('');
    }

    #[Test]
    public function a_named_key_has_a_name_and_a_key_state(): void
    {
        $named_key = new NamedKey('FooBarBaz');
        self::assertSame('FooBarBaz', $named_key->name);
        self::assertSame('named_key.FooBarBaz', (string)$named_key);
        self::assertSame('locks.FooBarBaz', (string)$named_key->key);
    }

    #[Test]
    public function a_named_key_prefixes_the_key_state(): void
    {
        $named_key = new NamedKey('FooBarBaz');
        self::assertSame('locks.FooBarBaz', (string)$named_key->key);

        $named_key = new NamedKey('locks.FooBarBaz');
        self::assertSame('locks.FooBarBaz', (string)$named_key->key);
    }

    #[Test]
    public function it_serializes_and_unserializes_a_named_key(): void
    {
        $named_key = (new NamedKeyFactory())->make('FooBarBaz');

        self::assertEquals($named_key, \unserialize(\serialize($named_key)));
    }
}
