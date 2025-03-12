<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param class-string<TransportInterface> $class
     * @param array<string,mixed> $options
     */
    public function __construct(
        public readonly string $class,
        public readonly string $connection,
        public readonly array $options = [],
    ) {
    }
}
