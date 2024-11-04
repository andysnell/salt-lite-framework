<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time;

/**
 * This class defines public constants (in the PHP sense) for time-related
 * constants (in the scientific, "Plank's Constant", pi, or e sense).
 */
class TimeConstant
{
    public const string NULL_DATETIME = '0000-00-00 00:00:00';

    public const string MYSQL_NULL_DATETIME = '1000-01-01 00:00:00';
    public const int NANOSECONDS_IN_SECOND = 1_000_000_000;
    public const int NANOSECONDS_IN_MILLISECOND = 1_000_000;
    public const int NANOSECONDS_IN_MICROSECOND = 1000;
    public const int MICROSECONDS_IN_SECOND = 1_000_000;
    public const int SECONDS_IN_MINUTE = 60;
    public const int MINUTES_IN_HOUR = 60;
    public const int SECONDS_IN_HOUR = 3600;
    public const int HOURS_IN_DAY = 24;
    public const int MINUTES_IN_DAY = 1440;
    public const int DAYS_IN_WEEK = 7;

    public const int|float SECONDS_IN_DAY = self::HOURS_IN_DAY * self::SECONDS_IN_HOUR;
    public const int|float MIN_SECONDS_IN_DAY = self::SECONDS_IN_DAY - 1;
    public const int|float MAX_SECONDS_IN_DAY = self::SECONDS_IN_DAY + 1;

    public const int MIN_DAYS_IN_MONTH = 28;
    public const int MAX_DAYS_IN_MONTH = 31;

    public const int MIN_ISO_WEEKS = 52;
    public const int MAX_ISO_WEEKS = 53;

    public const int MIN_DAYS_IN_YEAR = 365;
    public const int MAX_DAYS_IN_YEAR = 366;
    public const int|float MIN_SECONDS_IN_YEAR = self::SECONDS_IN_HOUR * self::HOURS_IN_DAY * self::MIN_DAYS_IN_YEAR;
    public const int|float MAX_SECONDS_IN_YEAR = self::SECONDS_IN_HOUR * self::HOURS_IN_DAY * self::MAX_DAYS_IN_YEAR;

    public const int|float SECONDS_IN_WEEK = self::SECONDS_IN_HOUR * self::HOURS_IN_DAY * self::DAYS_IN_WEEK;
}
