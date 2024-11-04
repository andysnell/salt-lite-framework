<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

use PhoneBurner\SaltLite\Framework\Domain\Email\EmailAddress;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

#[Contract]
interface MailableMessage extends Mailable
{
    public const string DEFAULT_CHARSET = 'utf-8';

    public function getPriority(): Priority;

    /**
     * @return array<EmailAddress>
     */
    public function getCc(): array;

    /**
     * @return array<EmailAddress>
     */
    public function getBcc(): array;

    /**
     * @return array<EmailAddress>
     */
    public function getFrom(): array;

    /**
     * @return array<EmailAddress>
     */
    public function getReplyTo(): array;

    /**
     * @return array<Attachment>
     */
    public function getAttachments(): array;
}
