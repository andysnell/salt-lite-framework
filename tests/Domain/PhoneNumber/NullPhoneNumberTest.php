<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber;

use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\NullPhoneNumber;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullPhoneNumberTest extends TestCase
{
    #[Test]
    public function NullPhoneNumber_represents_empty_phone_number(): void
    {
        $sut = NullPhoneNumber::make();
        self::assertNull($sut->toE164());
        self::assertSame($sut, $sut->getPhoneNumber());
        self::assertSame($sut, NullPhoneNumber::make());
    }
}
