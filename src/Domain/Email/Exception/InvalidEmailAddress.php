<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Email\Exception;

class InvalidEmailAddress extends \UnexpectedValueException
{
    public function __construct(
        string $address = '',
        string $message = 'Invalid RFC 822/RFC 5322 Email Address',
        int $code = 0,
        \Throwable|null $previous = null,
    ) {
        if ($address) {
            $message .= ": $address";
        }
        parent::__construct($message, $code, $previous);
    }
}
