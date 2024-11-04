<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\Domain;

use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;

/**
 * Represents a static file for an asset on the *local* file system that can be
 * served via our routing system.
 */
final readonly class StaticFile
{
    /**
     * @param non-empty-string $path
     * @param ContentType::*&string $content_type
     */
    public function __construct(
        public string $path,
        public string $content_type = ContentType::OCTET_STREAM,
    ) {
    }
}
