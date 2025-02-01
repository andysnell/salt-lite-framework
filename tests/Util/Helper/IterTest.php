<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use PhoneBurner\SaltLite\Framework\Domain\Arrayable;
use PhoneBurner\SaltLite\Framework\Util\Helper\Iter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IterTest extends TestCase
{
    /**
     * @param Arrayable|iterable<mixed> $input
     * @param array<mixed> $array
     */
    #[DataProvider('providesArrayAndIteratorTestCases')]
    #[Test]
    public function iterator_returns_an_Iterator_from_iterable(mixed $input, array $array): void
    {
        $converted = Iter::cast($input);
        self::assertInstanceOf(\Iterator::class, $converted);
        self::assertSame($array, \iterator_to_array($converted));
        if ($input instanceof \Iterator) {
            self::assertSame($input, $converted);
        }
    }

    /**
     * @return \Generator<array>
     */
    public static function providesArrayAndIteratorTestCases(): \Generator
    {
        $test_arrays = [
            'empty' => [],
            'simple' => [1, 2, 3],
            'associative' => ['foo' => 1, 'bar' => 2, 'baz' => 3],
            'non-sequential' => [0 => 'foo', 42 => 'bar', 23 => 'baz'],
        ];

        $test = static fn($input, array $array): array => ['input' => $input, 'array' => $array];
        foreach ($test_arrays as $type => $array) {
            yield 'array_' . $type => $test($array, $array);
            yield 'generator_' . $type => $test((static fn() => yield from $array)(), $array);
            yield 'iterator_' . $type => $test(new \ArrayIterator($array), $array);
            yield 'iterator_aggregate' . $type => $test(self::makeIteratorAggregate($array), $array);
            yield 'arrayable_' . $type => $test(self::makeArrayable($array), $array);
        }
    }

    /**
     * @param array<mixed> $array
     * @return \IteratorAggregate<mixed>
     */
    public static function makeIteratorAggregate(array $array): \IteratorAggregate
    {
        return new class ($array) implements \IteratorAggregate {
            /**
             * @param array<mixed> $array
             */
            public function __construct(private readonly array $array)
            {
            }

            /**
             * @return \Generator<mixed>
             */
            public function getIterator(): \Generator
            {
                yield from $this->array;
            }
        };
    }

    /**
     * @param array<mixed> $arrayable_array
     * @param array<mixed> $iterator_array
     * @return Arrayable&\IteratorAggregate<mixed>
     */
    public static function makeIterableArrayable(array $arrayable_array, array $iterator_array): object
    {
        return new class ($arrayable_array, $iterator_array) implements Arrayable, \IteratorAggregate {
            /**
             * @param array<mixed> $arrayable_array
             * @param array<mixed> $iterator_array
             */
            public function __construct(private readonly array $arrayable_array, private readonly array $iterator_array)
            {
            }

            /**
             * @return array<mixed>
             */
            public function toArray(): array
            {
                return $this->arrayable_array;
            }

            /**
             * @return \Generator<mixed>
             */
            public function getIterator(): \Generator
            {
                yield from $this->iterator_array;
            }
        };
    }

    /**
     * @param array<mixed> $array
     */
    public static function makeArrayable(array $array): Arrayable
    {
        return new class ($array) implements Arrayable {
            /**
             * @param array<mixed> $array
             */
            public function __construct(private readonly array $array)
            {
            }

            /**
             * @return array<mixed>
             */
            public function toArray(): array
            {
                return $this->array;
            }
        };
    }
}
