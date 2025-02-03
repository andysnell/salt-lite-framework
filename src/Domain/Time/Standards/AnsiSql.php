<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\Standards;

final class AnsiSql
{
    public const string DATETIME = 'Y-m-d H:i:s';
    public const string DATE = 'Y-m-d';
    public const string TIME = 'H:i:s';
    public const string YEAR = 'Y';

    public const string NULL_DATETIME = '0000-00-00 00:00:00';
    public const string NULL_DATE = '0000-00-00';
    public const string NULL_TIME = '00:00:00';
}
