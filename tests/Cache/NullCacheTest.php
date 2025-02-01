<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache;

use PhoneBurner\SaltLite\Framework\Cache\NullCache;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class NullCacheTest extends TestCase
{
    #[Test]
    public function it_is_a_null_object_version_of_our_cache(): void
    {
        $cache = new NullCache();
        self::assertNull($cache->get('key'));
        self::assertTrue($cache->set('key', 'value', Ttl::seconds(60)));
        self::assertTrue($cache->delete('key'));
        self::assertNull($cache->forget('key'));
        self::assertSame('value', $cache->remember('key', fn(): string => 'value', Ttl::seconds(60)));
    }
}
