<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Session;

use PhoneBurner\SaltLite\Framework\Http\Session\SessionId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class SessionIdTest extends TestCase
{
    #[Test]
    public function session_id_encodes_and_decodes_with_hex_encoding_by_default(): void
    {
        $raw_bytes = \random_bytes(SessionId::LENGTH);
        $hex_bytes = \bin2hex($raw_bytes);
        $id = new SessionId($raw_bytes);

        self::assertSame($raw_bytes, $id->bytes());
        self::assertEquals($id, SessionId::import($hex_bytes));
        self::assertEquals($id, SessionId::tryImport($hex_bytes));
        self::assertSame($hex_bytes, $id->export());
        self::assertSame($hex_bytes, (string)$id);
        self::assertSame(SessionId::LENGTH, $id->length());
        self::assertEquals($id, \unserialize(\serialize($id)));
        self::assertSame($hex_bytes, $id->jsonSerialize());

        $id2 = SessionId::generate();
        self::assertNotEquals($id, $id2);
        self::assertTrue(\ctype_xdigit($id2->export()));
        self::assertSame(SessionId::LENGTH, $id2->length());
    }

    #[Test]
    #[TestWith([''], 'empty_string')]
    #[TestWith(['invalid_hex'], 'invalid_hex')]
    #[TestWith([null], 'null')]
    #[TestWith(['deadbee'], 'odd_length_hex')]
    public function tryImport_returns_null_on_invalid_input(string|null $input): void
    {
        self::assertNull(SessionId::tryImport($input));
    }
}
