<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Lock;

use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use Symfony\Component\Lock\Key;

final readonly class NamedKey implements \Stringable
{
    public Key $key;

    public function __construct(public string $name)
    {
        $name || throw new \InvalidArgumentException('The name cannot be empty.');
        $this->key = new Key(Str::start($name, 'locks.'));
    }

    #[\Override]
    public function __toString(): string
    {
        return 'named_key.' . $this->name;
    }

    public function __serialize(): array
    {
        return [
            'name' => $this->name,
            'key' => $this->key,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->name = $data['name'];
        $this->key = $data['key'];
    }
}
