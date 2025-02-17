<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util;

use PhoneBurner\SaltLite\Framework\Util\ClassString;
use PhoneBurner\SaltLite\Framework\Util\ClassStringType;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\KeyManagement\Key;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\BinaryString;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\String\Traits\BinaryStringProhibitsSerialization;
use PhoneBurner\SaltLite\Framework\Util\Cryptography\Symmetric\SharedKey;
use PhoneBurner\SaltLite\Framework\Util\Encoding;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ClassStringTest extends TestCase
{
    #[Test]
    public function happy_path_test_enum(): void
    {
        $sut = new ClassString(Encoding::class);
        self::assertSame(Encoding::class, (string)$sut);
        self::assertSame(Encoding::class, $sut->value);
        self::assertSame(ClassStringType::Enum, $sut->type);
        self::assertTrue($sut->is(Encoding::Base64));
        self::assertTrue($sut->is(Encoding::class));
        self::assertFalse($sut->is(ClassStringType::class));
        self::assertFalse($sut->is(ClassStringType::Enum));
        self::assertSame(Encoding::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));
    }

    #[Test]
    public function happy_path_test_interface(): void
    {
        $sut = new ClassString(Key::class);
        self::assertSame(Key::class, (string)$sut);
        self::assertSame(Key::class, $sut->value);
        self::assertSame(ClassStringType::Interface, $sut->type);
        self::assertTrue($sut->is(Key::class));
        self::assertTrue($sut->is(BinaryString::class));
        self::assertFalse($sut->is(SharedKey::generate()));
        self::assertFalse($sut->is(SharedKey::class));
        self::assertSame(Key::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));
    }

    #[Test]
    public function happy_path_test_class(): void
    {
        $sut = new ClassString(SharedKey::class);
        self::assertSame(SharedKey::class, (string)$sut);
        self::assertSame(SharedKey::class, $sut->value);
        self::assertSame(ClassStringType::Object, $sut->type);
        self::assertTrue($sut->is(SharedKey::class));
        self::assertTrue($sut->is(Key::class));
        self::assertTrue($sut->is(BinaryString::class));
        self::assertTrue($sut->is(SharedKey::generate()));
        self::assertFalse($sut->is(ClassStringType::class));
        self::assertFalse($sut->is(ClassStringType::Enum));
        self::assertSame(SharedKey::class, $sut->reflect()->getName());
        self::assertEquals($sut, \unserialize(\serialize($sut)));
    }

    #[Test]
    public function sad_path_string(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Class Foo does not exist");
        new ClassString('Foo');
    }

    #[Test]
    public function sad_path_trait(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Traits are not supported");
        new ClassString(BinaryStringProhibitsSerialization::class);
    }
}
