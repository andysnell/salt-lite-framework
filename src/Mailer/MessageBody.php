<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

readonly class MessageBody
{
    public function __construct(
        public MessageBodyPart|null $html = null,
        public MessageBodyPart|null $text = null,
    ) {
    }
}
