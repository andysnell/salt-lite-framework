<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface Mailer
{
    public function send(MailableMessage $message): void;
}
