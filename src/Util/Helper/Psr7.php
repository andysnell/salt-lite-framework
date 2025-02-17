<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Helper;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

abstract readonly class Psr7
{
    /**
     * @param ContentType::*&string $content_type
     */
    final public static function expects(MessageInterface $message, string $content_type): bool
    {
        $headers = \array_filter(\array_map(
            static fn(string $header): string => \strtolower($message->getHeaderLine($header)),
            [HttpHeader::ACCEPT, HttpHeader::CONTENT_TYPE],
        ));

        if (\array_any($headers, static fn(string $header): bool => \str_contains($header, $content_type))) {
            return true;
        }

        // Handle content types defined with a structured syntax suffix, e.g. application/vnd.api+json
        $suffix = match ($content_type) {
            ContentType::JSON => '+json',
            ContentType::GZ => '+gzip',
            ContentType::ZIP => '+zip',
            ContentType::XML => '+xml',
            default => false,
        };

        return $suffix && \array_any($headers, static fn(string $header): bool => \str_contains($header, $suffix));
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
    final public static function attribute(string $class, ServerRequestInterface $request): object|null
    {
        $attribute = $request->getAttribute($class);
        return $attribute instanceof $class ? $attribute : null;
    }

    final public static function jsonBodyToArray(MessageInterface|StreamInterface $message): array|null
    {
        try {
            $stream = $message instanceof MessageInterface ? $message->getBody() : $message;
            $decoded = \json_decode((string)$stream, true, 512, \JSON_THROW_ON_ERROR);
            return \is_array($decoded) ? $decoded : null;
        } catch (\JsonException) {
            return null;
        }
    }
}
