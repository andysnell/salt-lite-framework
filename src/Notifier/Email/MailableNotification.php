<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Notifier\Email;

use PhoneBurner\SaltLite\Attribute\Usage\Contract;
use PhoneBurner\SaltLite\Mailer\Mailable;

/**
 * Used for "simple" email notifications that don't require additional headers
 * or attachments, and will be sent with the default global "from" address.
 */
#[Contract]
interface MailableNotification extends Mailable
{
}
