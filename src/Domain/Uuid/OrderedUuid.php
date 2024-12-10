<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Compliments `Uuid::ordered()` as this can be used in constant expressions
 * where `new OrderedUuid()` is allowed and `Uuid::ordered()` is not.
 */
final readonly class OrderedUuid implements UuidInterface
{
    use UuidStringWrapper;

    public function __construct()
    {
        $this->uuid = Uuid::ordered()->toString();
    }
}
