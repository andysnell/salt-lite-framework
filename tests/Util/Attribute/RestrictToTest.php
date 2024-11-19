<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Attribute;

use PhoneBurner\SaltLite\Framework\Container\ServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\RestrictTo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;

class RestrictToTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $sut = new RestrictTo(ServiceProvider::class);
        self::assertSame([ServiceProvider::class], $sut->classes);
    }

    #[Test]
    public function happy_path_with_multiple_classes(): void
    {
        $sut = new RestrictTo(ServiceProvider::class, ContainerInterface::class, RequestInterface::class);
        self::assertSame([
            ServiceProvider::class,
            ContainerInterface::class,
            RequestInterface::class,
        ], $sut->classes);
    }

    #[Test]
    public function sad_path(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new RestrictTo();
    }
}
