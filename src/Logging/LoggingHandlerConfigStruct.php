<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;

readonly class LoggingHandlerConfigStruct implements ConfigStruct
{
    public function __construct(
        public string $handler_class,
        public array|null $handler_options,
        public string|null $formatter_class = null,
        public array|null $formatter_options = [],
    ) {
    }

    public function __serialize(): array
    {
        return [
            $this->handler_class,
            $this->handler_options,
            $this->formatter_class,
            $this->formatter_options,
        ];
    }

    public function __unserialize(array $data): void
    {
        [
            $this->handler_class,
            $this->handler_options,
            $this->formatter_class,
            $this->formatter_options,
        ] = $data;
    }
}
