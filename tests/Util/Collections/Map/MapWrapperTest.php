<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Collections\Map;

use PhoneBurner\SaltLite\Framework\Container\Exception\NotFound;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\KeyValueStore;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\MapWrapper;
use PhoneBurner\SaltLite\Framework\Util\Collections\MapCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MapWrapperTest extends TestCase
{
    /** @return MapCollection<mixed> */
    public static function getMockMap(array $data = []): MapCollection
    {
        /** @var MapCollection<mixed> $map */
        $map = new class ($data) implements MapCollection {
            use MapWrapper;

            private readonly KeyValueStore $map;

            public function __construct(array $data = [])
            {
                $this->map = new KeyValueStore($data);
            }

            /** @return MapCollection<mixed> $map */
            protected function wrapped(): MapCollection
            {
                return $this->map;
            }
        };

        return $map;
    }

    #[Test]
    public function it_can_get_and_find_values(): void
    {
        $map = self::getMockMap(['key1' => 'value1', 'key2' => 'value2']);
        $this->assertSame('value1', $map->get('key1'));
        $this->assertSame('value2', $map->get('key2'));
        $this->assertTrue($map->has('key1'));
        $this->assertFalse($map->has('key3'));
        $this->assertNull($map->find('invalid_key'));

        $this->expectException(NotFound::class);
        $this->assertNull($map->get('invalid_key'));
    }

    #[Test]
    public function it_can_set_values(): void
    {
        $map = self::getMockMap();
        $map->set('key1', 'value1');

        $this->assertSame('value1', $map->get('key1'));
        $map->set('key2', 'value2');

        $this->assertSame('value2', $map->get('key2'));
    }

    #[Test]
    public function it_can_unset_values(): void
    {
        $map = self::getMockMap(['key1' => 'value1', 'key2' => 'value2']);
        $map->unset('key1');

        $this->assertNull($map->find('key1'));
        $this->assertSame('value2', $map->get('key2'));
    }

    #[Test]
    public function it_can_clear_the_mapped_values(): void
    {
        $map = self::getMockMap(['key1' => 'value1', 'key2' => 'value2']);
        $map->clear();

        $this->assertFalse($map->has('key1'));
        $this->assertFalse($map->has('key2'));
        $this->assertSame([], $map->toArray());
    }

    #[Test]
    public function it_can_return_the_data_as_an_array(): void
    {
        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $map = self::getMockMap(['key1' => 'value1', 'key2' => 'value2']);

        $this->assertSame($array, $map->toArray());
    }

    #[Test]
    public function it_can_replace_the_mapped_values(): void
    {
        $map = self::getMockMap(['key1' => 'value1', 'key2' => 'value2']);
        $map->replace(['key3' => 'value3']);

        $this->assertSame(['key3' => 'value3'], $map->toArray());
    }
}
