<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Monolog\Level as MonologLogLevel;
use Psr\Log\LogLevel as Psr3LogLevel;

enum LogLevel: string
{
    case Emergency = Psr3LogLevel::EMERGENCY;
    case Alert = Psr3LogLevel::ALERT;
    case Critical = Psr3LogLevel::CRITICAL;
    case Error = Psr3LogLevel::ERROR;
    case Warning = Psr3LogLevel::WARNING;
    case Notice = Psr3LogLevel::NOTICE;
    case Info = Psr3LogLevel::INFO;
    case Debug = Psr3LogLevel::DEBUG;

    public static function cast(mixed $value): self
    {
        return match (true) {
            $value instanceof self => $value,
            $value instanceof MonologLogLevel => self::from($value->toPsrLogLevel()),
            \is_string($value) => self::from(\strtolower($value)),
            \is_int($value) => self::tryFrom(
                MonologLogLevel::tryFrom($value)?->toPsrLogLevel() ?? throw new \InvalidArgumentException(),
            ),
            default => throw new \InvalidArgumentException(),
        };
    }

    public function toMonlogLogLevel(): MonologLogLevel
    {
        return MonologLogLevel::{$this->name};
    }
}
