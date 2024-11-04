<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Email;

use PhoneBurner\SaltLite\Framework\Domain\Email\Exception\InvalidEmailAddress;
use PhoneBurner\SaltLite\Framework\Domain\PhpSerializable;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

/**
 * @implements PhpSerializable<array{email_address: string}>
 */
#[Contract]
readonly class EmailAddress implements PhpSerializable, \JsonSerializable, \Stringable, EmailAddressAware
{
    private const string REGEX = '/(?<name>[^<]*)<(?<address>.*)>[^>]*/';

    public function __construct(
        public string $address,
        public string $name = '',
    ) {
        \filter_var($address, \FILTER_VALIDATE_EMAIL) ?: throw new InvalidEmailAddress($address);
    }

    public static function parse(self|string $address): self
    {
        if ($address instanceof self) {
            return $address;
        }

        if (! \str_contains($address, '<')) {
            return new self(\trim($address));
        }

        if (! \preg_match(self::REGEX, $address, $matches)) {
            throw new InvalidEmailAddress($address, 'Could not parse email address');
        }

        return new self(
            \trim($matches['address']),
            \trim(\str_replace(["\n", "\r"], '', $matches['name'])),
        );
    }

    #[\Override]
    public function __serialize(): array
    {
        return ['email_address' => (string)$this];
    }

    #[\Override]
    public function __unserialize(array $data): void
    {
        $email = self::parse($data['email_address']);
        $this->address = $email->address;
        $this->name = $email->name;
    }

    #[\Override]
    public function __toString(): string
    {
        if ($this->name) {
            return "{$this->name} <{$this->address}>";
        }

        return $this->address;
    }

    #[\Override]
    public function jsonSerialize(): string
    {
        return (string)$this;
    }

    #[\Override]
    public function getEmailAddress(): self
    {
        return $this;
    }
}
