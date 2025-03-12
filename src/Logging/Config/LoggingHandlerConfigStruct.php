<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Logging\LogLevel;

readonly class LoggingHandlerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public string $handler_class,
        public array $handler_options = [],
        public string|null $formatter_class = null,
        public array $formatter_options = [],
        public LogLevel $level = LogLevel::Debug,
        public bool $bubble = true,
    ) {
    }
}
