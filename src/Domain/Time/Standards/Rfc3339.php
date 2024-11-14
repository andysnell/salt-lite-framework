<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\Standards;

/**
 * "Date and Time on the Internet: Timestamps" Standard
 * (Necessary since PHP lacks a built-in constant for RFC3339 with microseconds)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3339
 */
final class Rfc3339
{
    public const string DATETIME = \DATE_RFC3339;
    public const string DATETIME_MILLISECOND = \DATE_RFC3339_EXTENDED;
    public const string DATETIME_MICROSECOND = 'Y-m-d\TH:i:s.uP';
}
