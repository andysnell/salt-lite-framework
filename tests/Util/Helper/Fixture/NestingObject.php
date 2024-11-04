<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture;

class NestingObject
{
    public int $foo = 42;

    /** @phpstan-ignore-next-line intentional unused private property */
    private string $password = 'hunter2';

    /**
     * @var array<NestedObject>
     */
    public array $nested_object_array;

    public function __construct(/** @phpstan-ignore-next-line intentional unused private property */
        private readonly NestedObject $private_nested_object,
        public NestedObject $public_nested_object,
        NestedObject ...$array,
    ) {
        $this->nested_object_array = $array;
    }
}
