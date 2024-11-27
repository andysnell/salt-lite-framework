<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Psr7
{
    public static function expects(MessageInterface $message, string $content_type): bool
    {
        if ($content_type === ContentType::JSON) {
            $content_type = 'json';
        }

        return \str_contains(\strtolower($message->getHeaderLine(HttpHeader::ACCEPT)), $content_type)
            || \str_contains(\strtolower($message->getHeaderLine(HttpHeader::CONTENT_TYPE)), $content_type);
    }

    public static function expectsJson(MessageInterface $message): bool
    {
        return self::expects($message, ContentType::JSON);
    }

    public static function expectsHtml(MessageInterface $message): bool
    {
        return self::expects($message, ContentType::HTML);
    }

    /**
     * If the request has an attribute with the name of the given class, and the
     * attribute's value is an instance of that class, return the attribute.
     * Otherwise, return null
     *
     * @template T of object
     * @param class-string<T> $class
     * @return T|null
     */
    public static function attribute(ServerRequestInterface $request, string $class): object|null
    {
        $attribute = $request->getAttribute($class);
        return $attribute instanceof $class ? $attribute : null;
    }
}
