<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Uuid;

use PhoneBurner\SaltLite\Framework\Util\Helper\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Compliments `Uuid::random()` as this can be used in constant expressions
 * where `new RandomUuid()` is allowed and `Uuid::random()` is not.
 */
final readonly class RandomUuid implements UuidInterface
{
    use UuidStringWrapper;

    public function __construct()
    {
        $this->uuid = Uuid::random()->toString();
    }
}
