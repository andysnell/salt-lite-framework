<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache;

use PhoneBurner\SaltLite\Framework\Cache\CacheAdapter;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKey;
use PhoneBurner\SaltLite\Framework\Domain\Hash\Hash;
use PhoneBurner\SaltLite\Framework\Domain\Time\Ttl;
use PhoneBurner\SaltLite\Framework\Util\Helper\Reflect;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class CacheAdapterTest extends TestCase
{
    private ArrayAdapter $cache_pool;

    private CacheInterface $psr_cache;

    private CacheAdapter $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->cache_pool = new ArrayAdapter(storeSerialized: false);
        $this->psr_cache = new Psr16Cache($this->cache_pool);
        $this->sut = new CacheAdapter($this->psr_cache);
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function get_fetches_item_from_cache(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertSame('value', $this->sut->get($raw_key));
    }

    #[TestWith([0])]
    #[TestWith([1])]
    #[Test]
    public function getMultiple_fetches_items_from_cache_with_keys(int $column_key): void
    {
        $key_pairs = [...self::providesNormalizedKeys()];
        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertTrue($this->psr_cache->set($normalized_key, Hash::string($raw_key)));
        }

        $values = $this->sut->getMultiple(\array_column($key_pairs, $column_key));
        $values = [...$values];

        self::assertCount(\count($key_pairs), $values);
        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertEquals(Hash::string($raw_key), $values[$normalized_key]);
        }
    }

    #[DataProvider('providesNormalizedKeys')]
    #[Test]
    public function set_puts_item_into_cache(string|\Stringable $raw_key, string $normalized_key): void
    {
        $start_time_threshold = \microtime(true);
        self::assertTrue($this->sut->set($raw_key, new Ttl(60), 'value'));
        $end_time_threshold = \microtime(true);

        $expiries = Reflect::getProperty($this->cache_pool, 'expiries');
        self::assertGreaterThanOrEqual($start_time_threshold + 60, $expiries[$normalized_key] ?? 0);
        self::assertLessThanOrEqual($end_time_threshold + 60, $expiries[$normalized_key] ?? 0);
        self::assertSame('value', $this->psr_cache->get($normalized_key));
    }

    #[TestWith([0])]
    #[TestWith([1])]
    #[Test]
    public function setMultiple_puts_items_into_cache(int $column_key): void
    {
        $key_pairs = [...self::providesNormalizedKeys()];
        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertNull($this->psr_cache->get($normalized_key));
        }

        $values = (static function (iterable $key_pairs) use ($column_key): \Generator {
            foreach ($key_pairs as [$raw_key, $normalized_key]) {
                yield $column_key ? $normalized_key : $raw_key => Hash::string($raw_key);
            }
        })($key_pairs);

        $start_time_threshold = \microtime(true);
        self::assertTrue($this->sut->setMultiple(new Ttl(60), $values));
        $end_time_threshold = \microtime(true);

        $expiries = Reflect::getProperty($this->cache_pool, 'expiries');
        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertGreaterThanOrEqual($start_time_threshold + 60, $expiries[$normalized_key] ?? 0);
            self::assertLessThanOrEqual($end_time_threshold + 60, $expiries[$normalized_key] ?? 0);
            self::assertEquals(Hash::string($raw_key), $this->psr_cache->get($normalized_key));
        }
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

    #[TestWith([0])]
    #[TestWith([1])]
    #[Test]
    public function deleteMultiple_removes_items_from_cache(int $column_key): void
    {
        $key_pairs = [...self::providesNormalizedKeys()];
        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertTrue($this->psr_cache->set($normalized_key, Hash::string($raw_key)));
        }

        self::assertTrue($this->sut->deleteMultiple(\array_column($key_pairs, $column_key)));

        foreach ($key_pairs as [$raw_key, $normalized_key]) {
            self::assertNull($this->sut->get($raw_key));
            self::assertNull($this->psr_cache->get($normalized_key));
        }
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
    public function remember_can_force_refresh_value(string|\Stringable $raw_key, string $normalized_key): void
    {
        self::assertTrue($this->psr_cache->set($normalized_key, 'value'));
        self::assertSame('new value', $this->sut->remember($raw_key, new Ttl(60), fn(): string => 'new value', true));
        self::assertSame('new value', $this->psr_cache->get($normalized_key));
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

    /**
     * @return \Generator<array{string|\Stringable, string}>
     */
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
                    return 'Stringable key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes';
                }
            },
            'stringable_key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes',
        ];
    }
}
