<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture;

class Mirror extends ShinyThing implements ReflectsLightWaves
{
    public const RED = 1;

    public const BLUE = 2;

    public const GREEN = 3;

    protected const YELLOW = 'this is protected';

    /**
     * @phpstan-ignore classConstant.unused
     */
    private const string PURPLE = 'this is private';

    private string $foo = 'foobar';
    private int $bar = 7654321;

    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @phpstan-ignore method.unused
     */
    private function getBar(): int
    {
        return $this->bar;
    }
}
