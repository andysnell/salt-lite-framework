<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

enum Priority: int
{
    case Highest = 1;
    case High = 2;
    case Normal = 3;
    case Low = 4;
    case Lowest = 5;
}
