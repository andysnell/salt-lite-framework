<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ObjectContainer;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Container\ObjectContainer\ImmutableObjectContainer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ImmutableObjectContainerTest extends TestCase
{
    #[Test]
    public function happy_path_tests(): void
    {
        $a = new \stdClass();
        $b = new \stdClass();
        $c = new \stdClass();

        $container = new ImmutableObjectContainer([
            'a' => $a,
            'b' => $b,
            'c' => $c,
        ]);

        self::assertCount(3, $container);
        self::assertSame($a, $container->get('a'));
        self::assertSame($b, $container->get('b'));
        self::assertSame($c, $container->get('c'));

        self::assertTrue($container->has('a'));
        self::assertTrue($container->has('b'));
        self::assertTrue($container->has('c'));

        self::assertSame(['a', 'b', 'c'], $container->keys());

        self::assertSame(['a' => $a, 'b' => $b, 'c' => $c], [...$container]);

        self::assertArrayHasKey('a', $container);
        self::assertSame($a, $container['a']);

        self::assertSame($b, $container->call(fn(): \stdClass => $b));
    }

    #[Test]
    public function throws_exception_when_key_not_found(): void
    {
        $container = new ImmutableObjectContainer([]);
        self::assertEmpty($container);
        self::assertSame([], $container->keys());
        self::assertSame([], [...$container]);
        self::assertFalse($container->has('a'));

        $this->expectException(NotFound::class);
        $container->get('a');
    }

    #[Test]
    public function throws_exception_when_setting_through_array_access(): void
    {
        $container = new ImmutableObjectContainer([]);

        $this->expectException(\LogicException::class);
        /** @phpstan-ignore-next-line */
        $container['a'] = new \stdClass();
    }

    #[Test]
    public function throws_exception_when_unsetting_through_array_access(): void
    {
        $container = new ImmutableObjectContainer([
            'a' => new \stdClass(),
        ]);

        $this->expectException(\LogicException::class);
        /** @phpstan-ignore-next-line */
        unset($container['a']);
    }
}
