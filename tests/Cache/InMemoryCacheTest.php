<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache;

use PhoneBurner\SaltLite\Framework\Cache\CacheAdapter;
use PhoneBurner\SaltLite\Framework\Cache\InMemoryCache;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKey;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class InMemoryCacheTest extends TestCase
{
    private CacheInterface $psr_cache;

    private CacheAdapter $sut;

    #[\Override]
    protected function setUp(): void
    {
        $array_cache = new ArrayAdapter(storeSerialized: false);
        $this->psr_cache = new Psr16Cache($array_cache);
        $this->sut = new InMemoryCache($array_cache);
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function get_fetches_item_from_cache(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertSame('value', $this->sut->get($raw_key));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function set_puts_item_into_cache(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->sut->set($raw_key, new Ttl(60), 'value'));
        self::assertSame('value', $this->psr_cache->get($normalized_key));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function set_puts_item_into_cache_with_ttl(string|\Stringable $raw_key, string $normalized_key): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects($this->once())
            ->method('set')
            ->with($normalized_key, 'value', 42)
            ->willReturn(true);

        self::assertTrue((new CacheAdapter($mock))->set($raw_key, new Ttl(42), 'value'));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function remember_returns_cached_item_if_exists(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertSame('value', $this->sut->remember($raw_key, new Ttl(60), fn(): string => 'new value'));
        self::assertSame('value', $this->psr_cache->get($normalized_key));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function remember_sets_cached_item_if_not_exists(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertNull($this->psr_cache->get($normalized_key));
        self::assertSame('new value', $this->sut->remember($raw_key, new Ttl(60), fn(): string => 'new value'));
        self::assertSame('new value', $this->psr_cache->get($normalized_key));
        self::assertSame('new value', $this->sut->get($raw_key));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function remember_sets_cached_item_with_ttl(string|\Stringable $raw_key, string $normalized_key): void
    {
        $mock = $this->createMock(CacheInterface::class);
        $mock->expects(self::once())
            ->method('get')
            ->with($normalized_key)
            ->willReturn(null);

        $mock->expects(self::once())
            ->method('set')
            ->with($normalized_key, 'better value', 3600)
            ->willReturn(true);

        self::assertSame('better value', (new CacheAdapter($mock))->remember($raw_key, new Ttl(3600), fn(): string => 'better value'));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function forget_gets_item_and_deletes_it(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertSame('value', $this->sut->forget($raw_key));
        self::assertNull($this->sut->get($raw_key));
        self::assertNull($this->psr_cache->get($normalized_key));
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function delete_removes_item_from_cache(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertTrue($this->sut->delete($raw_key));
        self::assertNull($this->sut->get($raw_key));
        self::assertNull($this->psr_cache->get($normalized_key));
    }

    public static function providesNormalizedKeys(): \Generator
    {
        yield ['key', 'key'];
        yield ['key_with_underscore', 'key_with_underscore'];
        yield ['key.with.dots', 'key.with.dots'];
        yield ['key with spaces', 'key_with_spaces'];
        yield ['key:with:colons', 'key_with_colons'];
        yield ['key{with}braces', 'key_with_braces'];
        yield ['key(with)parens', 'key_with_parens'];
        yield ['key/with/slashes', 'key_with_slashes'];
        yield ['key@with@at', 'key_with_at'];
        yield ['key\\with\\backslashes', 'key_with_backslashes'];
        yield ['key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes', 'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes'];
        yield [NamedKey::class . ':1234', 'phone_burner_salt_lite_framework_cache_lock_named_key_1234'];

        yield [
            new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes';
                }
            },
            'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes',
        ];
    }
}
