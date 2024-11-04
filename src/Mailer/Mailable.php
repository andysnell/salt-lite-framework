<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface Mailable
{
    /**
     * @return array<EmailAddress>
     */
    public function getTo(): array;

    public function getSubject(): string;

    public function getBody(): MessageBody|null;
}
