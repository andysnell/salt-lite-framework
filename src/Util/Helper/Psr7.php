<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use Psr\Http\Message\MessageInterface;

class Psr7
{
    public static function expectsJson(MessageInterface $message): bool
    {
        return \str_contains(\strtolower($message->getHeaderLine(HttpHeader::ACCEPT)), 'json')
            || \str_contains(\strtolower($message->getHeaderLine(HttpHeader::CONTENT_TYPE)), 'json');
    }

    public static function expectsHtml(MessageInterface $message): bool
    {
        return \str_contains(\strtolower($message->getHeaderLine(HttpHeader::ACCEPT)), ContentType::HTML)
            || \str_contains(\strtolower($message->getHeaderLine(HttpHeader::CONTENT_TYPE)), ContentType::HTML);
    }
}
