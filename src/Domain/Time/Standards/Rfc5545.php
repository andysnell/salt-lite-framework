<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time\Standards;

/**
 * Internet Calendaring and Scheduling Core Object Specification (iCalendar/ICS)
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5545
 */
final class Rfc5545
{
    public const string DATETIME = 'Ymd\THis\Z';
    public const string DATE = 'Ymd';
}
