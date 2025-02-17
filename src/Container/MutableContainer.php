<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use Psr\Container\ContainerInterface;

#[Contract]
interface MutableContainer extends ContainerInterface
{
    public function has(\Stringable|string $id): bool;

    public function get(\Stringable|string $id): mixed;

    public function set(\Stringable|string $id, mixed $value): void;

    public function unset(\Stringable|string $id): void;
}
