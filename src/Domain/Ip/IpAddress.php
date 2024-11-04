<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Ip;

class IpAddress implements \Stringable
{
    public function __construct(public readonly string $value)
    {
        \filter_var($value, \FILTER_VALIDATE_IP) ?: throw new \InvalidArgumentException('invalid ip address: ' . $value);
    }

    public function getType(): IpAddressType
    {
        return \str_contains($this->value, ':') ? IpAddressType::IPv6 : IpAddressType::IPv4;
    }

    public static function make(string $address): self
    {
        return new self($address);
    }

    public static function tryFrom(mixed $address): self|null
    {
        if ($address instanceof self) {
            return $address;
        }

        if ($address instanceof \Stringable) {
            $address = (string)$address;
        }

        if (! \is_string($address)) {
            return null;
        }

        try {
            return new self($address);
        } catch (\InvalidArgumentException) {
            return null;
        }
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->value;
    }

    public static function marshall(array $data): self|null
    {
        $addresses = $data['HTTP_TRUE_CLIENT_IP']
            ?? $data['HTTP_X_FORWARDED_FOR']
            ?? $data['REMOTE_ADDR']
            ?? null;

        if ($addresses === null) {
            return null;
        }

        // use left-most address since the ones to the right are the prox(y|ies).
        $addresses = \explode(',', (string)$addresses);

        return self::tryFrom(\reset($addresses));
    }

    public static function local(): self
    {
        return new self(\gethostbyname(\gethostname() ?: 'localhost'));
    }
}
