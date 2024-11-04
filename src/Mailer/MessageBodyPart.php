<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Mailer;

readonly class MessageBodyPart
{
    public const string DEFAULT_CHARSET = 'utf-8';

    public function __construct(
        public string $contents = '',
        public string $charset = self::DEFAULT_CHARSET,
    ) {
    }
}
