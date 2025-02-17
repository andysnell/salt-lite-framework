<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Attribute;

use PhoneBurner\SaltLite\Framework\ApplicationServiceProvider;
use PhoneBurner\SaltLite\Framework\Util\Attribute\DefaultServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DefaultServiceProviderTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $sut = new DefaultServiceProvider(ApplicationServiceProvider::class);
        self::assertSame(ApplicationServiceProvider::class, $sut->service_provider);
        self::assertSame(ApplicationServiceProvider::class, (string)$sut->mapsTo());
        self::assertSame(ApplicationServiceProvider::class, $sut->mapsTo()->value);
    }
}
