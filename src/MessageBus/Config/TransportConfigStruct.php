<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus\Config;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructSerialization;
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
