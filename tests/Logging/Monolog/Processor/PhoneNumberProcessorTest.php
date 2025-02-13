<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Logging\Monolog\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\DomesticPhoneNumber;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\NullPhoneNumber;
use PhoneBurner\SaltLite\Framework\Logging\Monolog\Processor\PhoneNumberProcessor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhoneNumberProcessorTest extends TestCase
{
    #[Test]
    public function context_without_phone_number(): void
    {
        $record = new LogRecord(
            new \DateTimeImmutable(),
            'test-channel',
            Level::Debug,
            'this is a test message',
            [
                'foo' => 'bar',
                'member' => new \stdClass(),
            ],
        );

        $sut = new PhoneNumberProcessor();

        self::assertSame($record, $sut($record));
    }

    #[Test]
    public function context_with_null_phone_number(): void
    {
        $now = new \DateTimeImmutable();
        $record = new LogRecord(
            $now,
            'test-channel',
            Level::Debug,
            'this is a test message',
            [
                'foo' => 'bar',
                'phone_number' => new NullPhoneNumber(),
                'member' => new \stdClass(),
            ],
        );

        $sut = new PhoneNumberProcessor();

        self::assertEquals(new LogRecord(
            $now,
            'test-channel',
            Level::Debug,
            'this is a test message',
            [
                'foo' => 'bar',
                'phone_number' => null,
                'member' => new \stdClass(),
            ],
        ), $sut($record));
    }

    #[Test]
    public function context_with_phone_number(): void
    {
        $now = new \DateTimeImmutable();
        $record = new LogRecord(
            $now,
            'test-channel',
            Level::Debug,
            'this is a test message',
            [
                'foo' => 'bar',
                'phone_number' => DomesticPhoneNumber::make('3145551234'),
                'member' => new \stdClass(),
            ],
        );

        $sut = new PhoneNumberProcessor();

        self::assertEquals(new LogRecord(
            $now,
            'test-channel',
            Level::Debug,
            'this is a test message',
            [
                'foo' => 'bar',
                'phone_number' => '+13145551234',
                'member' => new \stdClass(),
            ],
        ), $sut($record));
    }
}
