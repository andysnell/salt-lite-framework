<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Session;

use PhoneBurner\SaltLite\Framework\Http\Session\Handler\CookieSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\FileSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\InMemorySessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\NullSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\RedisSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandlerType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class SessionHandlerTypeTest extends TestCase
{
    #[Test]
    #[DataProvider('providesTestCases')]
    public function happyPathForGetSessionHandlerClass(string $class_string, SessionHandlerType $type): void
    {
        self::assertSame($class_string, $type->getSessionHandlerClass()->value);
    }

    public static function providesTestCases(): \Generator
    {
        yield [RedisSessionHandler::class, SessionHandlerType::Redis];
        yield [FileSessionHandler::class, SessionHandlerType::File];
        yield [CookieSessionHandler::class, SessionHandlerType::Cookie];
        yield [InMemorySessionHandler::class, SessionHandlerType::InMemory];
        yield [NullSessionHandler::class, SessionHandlerType::Null];
    }
}
