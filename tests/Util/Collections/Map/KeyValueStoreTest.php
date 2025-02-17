<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Collections\Map;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\KeyValueStore;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class KeyValueStoreTest extends TestCase
{
    #[Test]
    public function it_can_set_and_get_values(): void
    {
        $store = new KeyValueStore();

        $store->set('key', 'value');

        self::assertTrue($store->has('key'));
        self::assertSame('value', $store->get('key'));
        self::assertSame('value', $store->find('key'));
        self::assertTrue($store->contains('value'));

        self::assertFalse($store->has('non-existent-key'));
        self::assertFalse($store->contains('non-existent-value'));
        self::assertNull($store->find('non-existent-key'));

        $this->expectException(NotFound::class);
        self::assertNull($store->get('non-existent-key'));
    }

    #[Test]
    public function it_can_unset_a_value(): void
    {
        $store = new KeyValueStore(['key' => 'value']);
        self::assertTrue($store->has('key'));

        $store->unset('key');

        self::assertFalse($store->has('key'));
    }

    #[Test]
    public function it_can_remember_a_value_that_does_not_exist(): void
    {
        $store = new KeyValueStore();

        $result = $store->remember('key', static fn(): string => 'computed-value');

        self::assertSame('computed-value', $result);
        self::assertTrue($store->has('key'));
        self::assertSame('computed-value', $store->get('key'));
    }

    #[Test]
    public function it_can_remember_a_value_that_exists(): void
    {
        $store = new KeyValueStore(['key1' => 'value1', 'key2' => 'value2']);
        self::assertTrue($store->has('key2'));

        $result = $store->remember('key2', static fn(): string => 'computed-value');

        self::assertSame('value2', $result);
    }

    #[Test]
    public function it_can_forget_a_value(): void
    {
        $store = new KeyValueStore(['key' => 'value']);
        self::assertTrue($store->has('key'));

        $result = $store->forget('key');

        self::assertSame('value', $result);
        self::assertFalse($store->has('key'));
    }

    #[Test]
    public function it_can_clear_all_values(): void
    {
        $store = new KeyValueStore(['key1' => 'value1', 'key2' => 'value2']);
        self::assertSame(['key1' => 'value1', 'key2' => 'value2'], $store->toArray());
        self::assertFalse($store->isEmpty());
        self::assertCount(2, $store);

        $store->clear();

        self::assertSame([], $store->toArray());
        self::assertTrue($store->isEmpty());
        self::assertEmpty($store);
    }

    #[Test]
    public function it_can_iterate_over_values(): void
    {
        $store = new KeyValueStore(['key1' => 'value1', 'key2' => 'value2']);

        $values = [];
        foreach ($store as $key => $value) {
            $values[$key] = $value;
        }

        self::assertSame(['key1' => 'value1', 'key2' => 'value2'], $values);
    }

    #[Test]
    public function it_can_get_all_keys(): void
    {
        $store = new KeyValueStore(['key1' => 'value1', 'key2' => 'value2']);

        $keys = $store->keys();

        self::assertSame(['key1', 'key2'], $keys);
    }

    #[Test]
    public function it_can_filter_data(): void
    {
        $store = new KeyValueStore(['key1' => 1, 'key2' => 2, 'key3' => 3, 'key4' => 0]);

        $store->filter(static fn($value): bool => $value > 1);

        self::assertSame(['key2' => 2, 'key3' => 3], $store->toArray());
    }

    #[Test]
    public function it_can_reject_data(): void
    {
        $store = new KeyValueStore(['key1' => 1, 'key2' => 2, 'key3' => 3, 'key4' => 0]);

        $store->reject(static fn($value): bool => $value > 1);

        self::assertSame(['key1' => 1, 'key4' => 0], $store->toArray());
    }

    #[Test]
    public function it_can_map_data(): void
    {
        $store = new KeyValueStore(['key1' => 1, 'key2' => 2]);

        $mapped = $store->map(static fn($value): int|float => $value * 2);

        self::assertSame(['key1' => 2, 'key2' => 4], $mapped->toArray());
    }

    #[Test]
    public function it_can_all_data(): void
    {
        $store = new KeyValueStore(['key1' => 1, 'key2' => 2, 'key3' => 3, 'key4' => 0]);

        $result = $store->all(static fn($value): bool => $value > 0);

        self::assertFalse($result);

        $result = $store->all(static fn($value): bool => $value < 5);

        self::assertTrue($result);
    }

    #[Test]
    public function it_can_any_data(): void
    {
        $store = new KeyValueStore(['key1' => 1, 'key2' => 2, 'key3' => 3, 'key4' => 0]);

        $result = $store->any(static fn($value): bool => $value > 3);

        self::assertFalse($result);

        $result = $store->any(static fn($value): bool => $value > 2);

        self::assertTrue($result);
    }

    #[Test]
    public function it_can_serialize_and_unserialize(): void
    {
        $store = new KeyValueStore(['key' => 'value']);

        $serialized = \serialize($store);
        $unserialized = \unserialize($serialized);

        self::assertInstanceOf(KeyValueStore::class, $unserialized);
        self::assertSame(['key' => 'value'], $unserialized->toArray());
    }

    #[Test]
    public function it_can_check_if_empty(): void
    {
        $emptyStore = new KeyValueStore();
        $nonEmptyStore = new KeyValueStore(['key' => 'value']);

        self::assertTrue($emptyStore->isEmpty());
        self::assertFalse($nonEmptyStore->isEmpty());
    }

    #[Test]
    public function it_has_array_access_behavior(): void
    {
        $store = new KeyValueStore();

        $store['key'] = 'value';

        self::assertArrayHasKey('key', $store);
        self::assertArrayNotHasKey('key2', $store);
        self::assertSame('value', $store['key']);
        self::assertNull($store['non-existent-key']);

        unset($store['key']);
        unset($store['non-existent-key']);

        self::assertArrayNotHasKey('key', $store);
    }
}
