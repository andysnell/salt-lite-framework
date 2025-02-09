<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Container\ServiceContainer;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceContainer\ServiceContainerAdapter;
use PhoneBurner\SaltLite\Framework\Database\Doctrine\Orm\EntityManagerProvider;
use PhoneBurner\SaltLite\Framework\MessageBus\MessageBus;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ServiceContainerAdapterTest extends TestCase
{
    public function test_it_can_get_a_service(): void
    {
        $app = $this->createMock(App::class);

        $sut = new ServiceContainerAdapter($app);

        self::assertFalse($sut->has(MessageBus::class));
        self::assertFalse($sut->has(EntityManagerProvider::class, true));
        self::assertTrue($sut->has(EntityManagerProvider::class, false));

        /** @var MessageBus&MockObject $message_bus */
        $message_bus = $this->createMock(MessageBus::class);
        $sut->set(MessageBus::class, $message_bus);
        self::assertTrue($sut->has(MessageBus::class));
        self::assertSame($message_bus, $sut->get(MessageBus::class));

        /** @var EntityManagerProvider&MockObject $em_provider */
        $em_provider = $this->createMock(EntityManagerProvider::class);
        $sut->set(EntityManagerProvider::class, fn(App $app): EntityManagerProvider => $em_provider);
        self::assertTrue($sut->has(EntityManagerProvider::class, true));
        self::assertSame($em_provider, $sut->get(EntityManagerProvider::class));
    }
}
