<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer\Transport;

use PhoneBurner\SaltLite\Enum\WithStringBackedInstanceStaticMethod;

enum TransportDriver: string
{
    use WithStringBackedInstanceStaticMethod;

    case Smtp = 'smtp';
    case SendGrid = 'sendgrid';
    case None = 'none';
}
