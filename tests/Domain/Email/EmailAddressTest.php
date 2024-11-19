<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Email;

use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Domain\Email\Exception\InvalidEmailAddress;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
    private const string VALID_EMAIL = 'test@phoneburner.com';

    private const string VALID_NAME = 'John Doe';

    private const string VALID_FULL = 'John Doe <test@phoneburner.com>';

    #[Test]
    public function it_can_be_instantiated_with_just_address(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL);
        self::assertInstanceOf(EmailAddress::class, $email);

        self::assertSame($email, $email->getEmailAddress());
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame('', $email->name);
        self::assertSame(self::VALID_EMAIL, (string)$email);
        self::assertSame(self::VALID_EMAIL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function it_can_be_instantiated_with_address_and_name(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL, self::VALID_NAME);
        self::assertInstanceOf(EmailAddress::class, $email);

        self::assertSame($email, $email->getEmailAddress());
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame(self::VALID_NAME, $email->name);
        self::assertSame(self::VALID_FULL, (string)$email);
        self::assertSame(self::VALID_FULL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function parse_returns_email_address_from_address_alone(): void
    {
        $email = EmailAddress::parse(self::VALID_EMAIL);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame('', $email->name);
        self::assertSame(self::VALID_EMAIL, (string)$email);
        self::assertSame(self::VALID_EMAIL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function parse_returns_email_address_from_full_address(): void
    {
        $email = EmailAddress::parse(self::VALID_FULL);
        self::assertSame(self::VALID_EMAIL, $email->address);
        self::assertSame(self::VALID_NAME, $email->name);
        self::assertSame(self::VALID_FULL, (string)$email);
        self::assertSame(self::VALID_FULL, $email->jsonSerialize());

        $serialized = \serialize($email);
        self::assertEquals($email, \unserialize($serialized));
    }

    #[Test]
    public function parse_returns_self(): void
    {
        $email = new EmailAddress(self::VALID_EMAIL, self::VALID_NAME);
        self::assertSame($email, EmailAddress::parse($email));
    }

    #[TestWith([''])]
    #[TestWith(['john'])]
    #[TestWith(['john@'])]
    #[TestWith(['john@phoneburner'])]
    #[Test]
    public function invalid_email_results_in_exception(string $invalid_email): void
    {
        $this->expectException(InvalidEmailAddress::class);

        new EmailAddress($invalid_email);
    }
}
