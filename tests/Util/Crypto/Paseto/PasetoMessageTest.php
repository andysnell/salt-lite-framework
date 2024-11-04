<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Crypto\Paseto;

use PhoneBurner\SaltLite\Framework\Util\Crypto\Paseto\PasetoMessage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PasetoMessageTest extends TestCase
{
    #[Test]
    public function null_case(): void
    {
        $message = PasetoMessage::make([], [], []);

        self::assertEquals(new PasetoMessage(''), $message);
        self::assertSame('', $message->data);
        self::assertSame('', $message->footer);
        self::assertSame('', $message->implicit_claims);
        self::assertSame([], $message->getData());
        self::assertSame([], $message->getFooter());
        self::assertSame([], $message->getImplicitClaims());
    }

    #[Test]
    public function simple_case(): void
    {
        $message = PasetoMessage::make([
            'foo' => 42,
        ]);

        self::assertEquals(new PasetoMessage('{"foo":42}'), $message);
        self::assertSame('{"foo":42}', $message->data);
        self::assertSame('', $message->footer);
        self::assertSame('', $message->implicit_claims);
        self::assertSame(['foo' => 42], $message->getData());
        self::assertSame([], $message->getFooter());
        self::assertSame([], $message->getImplicitClaims());
    }

    #[Test]
    public function with_footer_and_implicit_claims(): void
    {
        $message = PasetoMessage::make(
            ['foo' => 42],
            ['bar' => 'baz'],
            ['qux' => 123],
        );

        self::assertEquals(new PasetoMessage('{"foo":42}', '{"bar":"baz"}', '{"qux":123}'), $message);
        self::assertSame('{"foo":42}', $message->data);
        self::assertSame('{"bar":"baz"}', $message->footer);
        self::assertSame('{"qux":123}', $message->implicit_claims);
        self::assertSame(['foo' => 42], $message->getData());
        self::assertSame(['bar' => 'baz'], $message->getFooter());
        self::assertSame(['qux' => 123], $message->getImplicitClaims());
    }
}
