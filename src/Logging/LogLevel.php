<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Logging;

use Psr\Log\LogLevel as PsrLogLevel;

enum LogLevel: string
{
    case Emergency = self::EMERGENCY;
    case Alert = self::ALERT;
    case Critical = self::CRITICAL;
    case Error = self::ERROR;
    case Warning = self::WARNING;
    case Notice = self::NOTICE;
    case Info = self::INFO;
    case Debug = self::DEBUG;

    public const string EMERGENCY = PsrLogLevel::EMERGENCY;
    public const string ALERT = PsrLogLevel::ALERT;
    public const string CRITICAL = PsrLogLevel::CRITICAL;
    public const string ERROR = PsrLogLevel::ERROR;
    public const string WARNING = PsrLogLevel::WARNING;
    public const string NOTICE = PsrLogLevel::NOTICE;
    public const string INFO = PsrLogLevel::INFO;
    public const string DEBUG = PsrLogLevel::DEBUG;
}
