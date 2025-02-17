<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Cryptography\Paseto;

final readonly class PasetoMessage
{
    public function __construct(
        public string $data,
        public string $footer = '',
        public string $implicit_claims = '',
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $footer
     * @param array<string, mixed> $implicit_claims
     */
    public static function make(array $data, array $footer = [], array $implicit_claims = []): self
    {
        return new self(
            self::encode($data),
            self::encode($footer),
            self::encode($implicit_claims),
        );
    }

    public function getData(): array
    {
        return self::decode($this->data);
    }

    public function getFooter(): array
    {
        return self::decode($this->footer);
    }

    public function getImplicitClaims(): array
    {
        return self::decode($this->implicit_claims);
    }

    private static function encode(array $value): string
    {
        return $value === [] ? '' : \json_encode($value, \JSON_THROW_ON_ERROR);
    }

    private static function decode(string $json): array
    {
        return $json === '' ? [] : (array)\json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
    }
}
