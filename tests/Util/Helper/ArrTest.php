<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Helper;

use ArrayAccess;
use ArrayIterator;
use Generator;
use Iterator;
use IteratorAggregate;
use IteratorIterator;
use PhoneBurner\SaltLite\Framework\Domain\Arrayable;
use PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture\NestedObject;
use PhoneBurner\SaltLite\Framework\Tests\Util\Helper\Fixture\NestingObject;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use PhoneBurner\SaltLite\Framework\Util\NullArrayAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReturnTypeWillChange;
use stdClass;

class ArrTest extends TestCase
{
    #[DataProvider('providesAccessibleTestCases')]
    #[Test]
    public function accessible_returns_if_array_or_implements_ArrayAccess(bool $expected, mixed $value): void
    {
        self::assertSame($expected, Arr::accessible($value));
    }

    /**
     * @return Generator<array>
     */
    public static function providesAccessibleTestCases(): Generator
    {
        yield [true, []];
        yield [true, [1, 2, 3]];
        yield [true, ['foo' => 1, 'bar' => 2, 'baz' => 3]];
        yield [true, new NullArrayAccess(['foo' => 1, 'bar' => 2, 'baz' => 3])];
        yield [true, static::makeArrayAccess([1, 2, 3, 4])];

        $not_arrays = [true, false, null, 123, 'hello world', static fn(): array => [1, 2, 3], new stdClass()];
        foreach ($not_arrays as $not_an_array) {
            yield [false, $not_an_array];
        }
    }

    #[DataProvider('providesArrayAndIteratorTestCases')]
    #[Test]
    public function arrayable_returns_true_if_array_or_castable_to_array(mixed $input, array $array): void
    {
        self::assertTrue(Arr::arrayable($array));
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function arrayable_returns_false_if_not_array_or_castable_to_array(mixed $value): void
    {
        self::assertFalse(Arr::arrayable($value));
    }

    /**
     * @param Arrayable|iterable<mixed> $input
     * @param array<mixed> $array
     */
    #[DataProvider('providesArrayAndIteratorTestCases')]
    #[Test]
    public function array_returns_array_from_arrays_or_arraylike(mixed $input, array $array): void
    {
        $converted = Arr::array($input);
        self::assertIsArray($converted);
        self::assertSame($array, $converted);
        if (\is_array($input)) {
            self::assertSame($input, $converted);
        }
    }

    /**
     * @param Arrayable|iterable<mixed> $input
     * @param array<mixed> $array
     */
    #[DataProvider('providesArrayAndIteratorTestCases')]
    #[Test]
    public function iterator_returns_an_Iterator_from_iterable(mixed $input, array $array): void
    {
        $converted = Arr::iterable($input);
        self::assertInstanceOf(Iterator::class, $converted);
        self::assertSame($array, \iterator_to_array($converted));
        if ($input instanceof Iterator) {
            self::assertSame($input, $converted);
        }
    }

    /**
     * @return Generator<array>
     */
    public static function providesArrayAndIteratorTestCases(): Generator
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
            yield 'iterator_' . $type => $test(new ArrayIterator($array), $array);
            yield 'iterator_aggregate' . $type => $test(static::makeIteratorAggregate($array), $array);
            yield 'arrayable_' . $type => $test(static::makeArrayable($array), $array);
        }
    }

    #[Test]
    public function iterator_returns_a_Traversable_before_Arrayable(): void
    {
        $iterable_and_arrayable = static::makeIterableArrayable(
            ['foo' => 1, 'bar' => 2, 'baz' => 3],
            [0 => 'foo', 42 => 'bar', 23 => 'baz'],
        );

        $converted = Arr::iterable($iterable_and_arrayable);
        self::assertInstanceOf(IteratorIterator::class, $converted);
        self::assertSame([0 => 'foo', 42 => 'bar', 23 => 'baz'], \iterator_to_array($converted));
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function array_throws_exception_for_non_arraylike_things(mixed $not_arraylike): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Arr::array($not_arraylike);
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function iterator_throws_exception_for_non_arraylike_things(mixed $not_arraylike): void
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line Intentional Defect to Test Sad Path */
        Arr::iterable($not_arraylike);
    }

    /**
     * @return Generator<array>
     */
    public static function providesArrayInvalidTestCases(): Generator
    {
        yield from \array_map(static fn($value): array => [$value], [
            true,
            false,
            null,
            123,
            'hello world',
            static fn(): array => [1, 2, 3],
            new stdClass(),
            static fn() => yield from [1, 2, 3], // callable is not generator until invoked
            new class {
                public int $a = 1;
                public int $b = 2;
                public int $c = 3;
            },
        ]);
    }

    /**
     * @param iterable<mixed>|Arrayable $iterable
     */
    #[DataProvider('providesFirstTestCases')]
    #[Test]
    public function first_returns_the_first_value_from_iterable(mixed $expected, mixed $iterable): void
    {
        self::assertSame($expected, Arr::first($iterable));
    }

    /**
     * @return Generator<array>
     */
    public static function providesFirstTestCases(): Generator
    {
        $tests = [
            'empty' => ['expected' => null, 'array' => []],
            'single' => ['expected' => 'foo', 'array' => ['foo']],
            'int_value' => ['expected' => 1, 'array' => [1, 2, 3]],
            'string_value' => ['expected' => '1', 'array' => ['1', '2', '3']],
            'assoc' => ['expected' => 333, 'array' => ['foo' => 333, 'bar' => 666, 'baz' => 999]],
            'non_sequential' => ['expected' => 333, 'array' => [43 => 333, 1 => 666, 0 => 999]],
        ];

        foreach ($tests as $type => $test) {
            yield 'array_' . $type => [$test['expected'], $test['array'],];
            yield 'generator_' . $type => [$test['expected'], (static fn() => yield from $test['array'])()];
            yield 'iterator_' . $type => [$test['expected'], new ArrayIterator($test['array'])];
            yield 'iterator_aggregate' . $type => [$test['expected'], static::makeIteratorAggregate($test['array'])];
            yield 'arrayable_' . $type => [$test['expected'], static::makeArrayable($test['array'])];
            yield 'iterable_arrayable' . $type => [$test['expected'], static::makeIterableArrayable(['error'], $test['array'])];
        }
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function first_throws_exception_for_non_arraylike_things(mixed $not_arraylike): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Arr::first($not_arraylike);
    }

    #[DataProvider('providesGetAndHasTestCases')]
    #[Test]
    public function has_traverses_array_by_dot_notation(string $needle, bool $exists): void
    {
        $array = $this->makeTestHaystack();
        $array_access = static::makeArrayAccess($array);

        self::assertSame($exists, Arr::has($needle, $array));
        self::assertSame($exists, Arr::has($needle, $array_access));
    }

    #[Test]
    public function has_handles_empty_array_case(): void
    {
        self::assertFalse(Arr::has('', []));
        self::assertFalse(Arr::has('foo', []));
        self::assertFalse(Arr::has('foo.bar', []));
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function has_throws_exception_for_non_arraylike_things(mixed $not_arraylike): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Arr::has('foo', $not_arraylike);
    }

    #[DataProvider('providesGetAndHasTestCases')]
    #[Test]
    public function get_traverses_array_by_dot_notation(
        string $needle,
        bool $exists,
        mixed $expected,
        mixed $default = null,
    ): void {
        $array = $this->makeTestHaystack();
        $array_access = static::makeArrayAccess($array);

        self::assertSame($expected, Arr::get($needle, $array, $default));
        self::assertSame($expected, Arr::get($needle, $array_access, $default));
    }

    #[Test]
    public function get_handles_empty_invalid_array_case(): void
    {
        self::assertNull(Arr::get('', []));
        self::assertNull(Arr::get('foo', []));
        self::assertNull(Arr::get('foo.bar', []));
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function get_throws_exception_for_non_arraylike_things(mixed $not_arraylike): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Arr::get('foo', $not_arraylike);
    }

    #[DataProvider('providesArrayInvalidTestCases')]
    #[Test]
    public function value_returns_same_non_arrayable_value(mixed $value): void
    {
        self::assertSame($value, Arr::value($value));
    }

    #[Test]
    public function value_recursively_casts_arrayable_values_to_array(): void
    {
        $anon_function_returns_array = static fn(): array => [1, 2, 3];
        $std_class = new stdClass();
        $anon_function_returns_iterable = static fn() => yield from [1, 2, 3];
        $anon_class_with_properties = new class {
            public int $a = 1;
            public int $b = 2;
            public int $c = 3;
        };

        $input = static::makeIteratorAggregate([
            'foo' => 'bar',
            'baz' => [1, 2, 3],
            'qux' => new ArrayIterator([
                [
                    'array' => [
                        ['a', 'b', 'c'],
                        static::makeArrayable([11, 12, 13]),
                        static::makeIteratorAggregate([21, 22, 23]),
                    ],
                    'iterator' => static::makeIteratorAggregate([
                        ['d', 'e', 'f'],
                        static::makeArrayable([11, 12, 13]),
                        static::makeIteratorAggregate([21, 22, 23]),
                    ]),
                    'arrayable' => static::makeArrayable([
                        ['g', 'h', 'i'],
                        static::makeArrayable([11, 12, 13]),
                        static::makeIteratorAggregate([21, 22, 23]),
                    ]),
                    'keys' => [
                        ['key_0' => 'a', 'key_1' => 'b', 'key_2' => 'c'],
                        static::makeArrayable(['key_0' => 11, 'key_1' => 12, 13]),
                        static::makeIteratorAggregate(['key_0' => 21, 'key_1' => 22, 'key_2' => 23]),
                    ],
                ],
            ]),
            'invalid' => [
                true,
                false,
                null,
                123,
                'hello world',
                $anon_function_returns_array,
                $std_class,
                $anon_function_returns_iterable, // callable is not generator until invoked
                $anon_class_with_properties,
            ],
        ]);

        self::assertSame([
            'foo' => 'bar',
            'baz' => [1, 2, 3],
            'qux' => [
                [
                    'array' => [
                        ['a', 'b', 'c'],
                        [11, 12, 13],
                        [21, 22, 23],
                    ],
                    'iterator' => [
                        ['d', 'e', 'f'],
                        [11, 12, 13],
                        [21, 22, 23],
                    ],
                    'arrayable' => [
                        ['g', 'h', 'i'],
                        [11, 12, 13],
                        [21, 22, 23],
                    ]
                    ,
                    'keys' => [
                        ['key_0' => 'a', 'key_1' => 'b', 'key_2' => 'c'],
                        ['key_0' => 11, 'key_1' => 12, 13],
                        ['key_0' => 21, 'key_1' => 22, 'key_2' => 23],
                    ],
                ],
            ],
            'invalid' => [
                true,
                false,
                null,
                123,
                'hello world',
                $anon_function_returns_array,
                $std_class,
                $anon_function_returns_iterable, // callable is not generator until invoked
                $anon_class_with_properties,
            ],
        ], Arr::value($input));
    }

    /**
     * @param array<mixed> $expected
     */
    #[DataProvider('providesWrapTestCases')]
    #[Test]
    public function wrap_returns_arrayable_cast_to_array_or_wraps_in_array_otherwise(
        mixed $input,
        array $expected,
    ): void {
        self::assertSame($expected, Arr::wrap($input));
    }

    /**
     * @return Generator<array>
     */
    public static function providesWrapTestCases(): Generator
    {
        $not_arrayable = [
            'null' => null,
            'bool_true' => true,
            'bool_false' => false,
            'int' => 1,
            'int_empty' => 0,
            'float' => 1.2,
            'float_empty' => 0.0,
            'string' => 'Hello World',
            'string_empty' => '',
            'object' => new stdClass(),
            'resource' => Str::stream()->detach(),
            'callable' => static fn(): int => 1,
        ];

        foreach ($not_arrayable as $test => $value) {
            yield $test => [$value, [$value]];
        }

        $arrayable = [
            'empty' => [],
            'list' => [1, 2, 3, 4, 5],
            'associative' => ['foo' => 1, 'bar' => 2, 'baz' => 'qux'],
        ];

        foreach ($arrayable as $test => $value) {
            yield $test . '_array' => [$value, $value];
            yield $test . '_arrayable' => [static::makeArrayable($value), $value];
            yield $test . '_iterator' => [static::makeIteratorAggregate($value), $value];
        }
    }

    /**
     * @return array<mixed,mixed>
     */
    public function makeTestHaystack(): array
    {
        return [
            'top_level_exists' => 'foo',
            'top_level_false' => false,
            'top_level_empty' => [],
            'top_level_null' => null,
            'foo' => [
                'bar' => "Hello, World!",
                'baz' => [
                    'foo' => 1234,
                    'bar' => true,
                    'baz' => false,
                    'qux' => null,
                ],
            ],
            'empty' => [
                'foo' => [],
            ],
            '.leading_dot' => 'leading_dot_value',
            null => [
                'hidden' => 'foobar_hidden',
                '' => [
                    null => [
                        '' => 'deep_hidden',
                        'deep' => 'deep_value',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return Generator<array>
     */
    public static function providesGetAndHasTestCases(): Generator
    {
        $t = static fn(string $needle, bool $exists, $expected, $default = null): array => \func_get_args();

        yield 'exists' => $t('top_level_exists', true, 'foo');
        yield 'exists:default' => $t('top_level_exists', true, 'foo', 'bar');
        yield 'false' => $t('top_level_false', true, false);
        yield 'false:default' => $t('top_level_false', true, false, true);
        yield 'empty' => $t('top_level_empty', true, []);
        yield 'empty:default' => $t('top_level_empty', true, [], ['foo']);
        yield 'null:default:null' => $t('top_level_null', false, null, null);
        yield 'null:default:string' => $t('top_level_null', false, 'Hello, World', "Hello, World");
        yield 'null:default:callable' => $t('top_level_null', false, 12345, static fn(): int => 12345);
        yield 'not_exists:default:null' => $t('not_exists', false, null, null);
        yield 'not_exists:default:string' => $t('not_exists', false, 'Hello, World', "Hello, World");
        yield 'not_exists:default:callable' => $t('not_exists', false, 12345, static fn(): int => 12345);
        yield 'not_exists_dot:default:null' => $t('not_exists.not_exists', false, null, null);
        yield 'not_exists_dot:default:string' => $t('not_exists.not_exists', false, 'Hello, World', "Hello, World");
        yield 'not_exists_dot:default:callable' => $t('not_exists.not_exists', false, 12345, static fn(): int => 12345);
        yield 'foo' => $t('foo', true, ['bar' => "Hello, World!", 'baz' => ['foo' => 1234, 'bar' => true, 'baz' => false, 'qux' => null]]);
        yield 'foo.bar' => $t('foo.bar', true, "Hello, World!");
        yield 'foo.baz' => $t('foo.baz', true, ['foo' => 1234, 'bar' => true, 'baz' => false, 'qux' => null]);
        yield 'foo.baz.foo' => $t('foo.baz.foo', true, 1234, 'error');
        yield 'foo.baz.bar' => $t('foo.baz.bar', true, true, 'error');
        yield 'foo.baz.baz' => $t('foo.baz.baz', true, false, 'error');
        yield 'foo.baz.qux' => $t('foo.baz.qux', false, null);
        yield 'foo.baz.qux:default' => $t('foo.baz.qux', false, 'default', 'default');
        yield 'foo.baz.not' => $t('foo.baz.not', false, null);
        yield 'foo.baz.not.nope' => $t('foo.baz.not.nope', false, null);
        yield 'foo.baz.not.nope.nah' => $t('foo.baz.not.nope.nah', false, null);
        yield 'foo.not.foo' => $t('foo.not.foo', false, null);
        yield 'empty_parent' => $t('empty', true, ['foo' => []]);
        yield 'empty_child' => $t('empty.foo', true, []);
        yield 'dot_string' => $t('.', true, [
            null => [
                '' => 'deep_hidden',
                'deep' => 'deep_value',
            ],
        ]);
        yield 'empty_string' => $t('', true, [
            'hidden' => 'foobar_hidden',
            '' => [
                null => [
                    '' => 'deep_hidden',
                    'deep' => 'deep_value',
                ],
            ],
        ]);
        yield 'leading_dot_key' => $t('.leading_dot', true, 'leading_dot_value');
        yield 'leading_dot_0' => $t('.hidden', true, 'foobar_hidden');
        yield 'leading_dot_1' => $t('...', true, 'deep_hidden');
        yield 'leading_dot_2' => $t('...deep', true, 'deep_value');
        yield 'leading_dot_3' => $t('.....', false, null);
        yield 'leading_dot_4' => $t('....deeper', false, null);
    }

    /**
     * @param array<mixed> $array
     * @return ArrayAccess<mixed,mixed>
     */
    public static function makeArrayAccess(array $array): ArrayAccess
    {
        return new class ($array) implements ArrayAccess {
            /**
             * @param array<mixed> $array
             */
            public function __construct(private array $array)
            {
            }

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->array[$offset]);
            }

            /**
             * @return mixed
             * @noinspection PhpLanguageLevelInspection
             */
            #[ReturnTypeWillChange]
            public function offsetGet(mixed $offset)
            {
                return $this->array[$offset];
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                $this->array[$offset] = $value;
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->array[$offset]);
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

    /**
     * @param array<mixed> $array
     * @return IteratorAggregate<mixed>
     */
    public static function makeIteratorAggregate(array $array): IteratorAggregate
    {
        return new class ($array) implements IteratorAggregate {
            /**
             * @param array<mixed> $array
             */
            public function __construct(private readonly array $array)
            {
            }

            /**
             * @return Generator<mixed>
             */
            public function getIterator(): Generator
            {
                yield from $this->array;
            }
        };
    }

    /**
     * @param array<mixed> $arrayable_array
     * @param array<mixed> $iterator_array
     * @return Arrayable&IteratorAggregate<mixed>
     */
    public static function makeIterableArrayable(array $arrayable_array, array $iterator_array): object
    {
        return new class ($arrayable_array, $iterator_array) implements Arrayable, IteratorAggregate {
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
             * @return Generator<mixed>
             */
            public function getIterator(): Generator
            {
                yield from $this->iterator_array;
            }
        };
    }

    /**
     * @param array<mixed> $array
     */
    #[DataProvider('providesAssociativeAndListArrays')]
    #[Test]
    public function isList_is_true_for_arrays_with_sequential_int_keys_starting_at_zero(
        array $array,
        bool $is_list,
    ): void {
        self::assertSame($is_list, Arr::isList($array));
    }

    /**
     * @return Generator<array>
     */
    public static function providesAssociativeAndListArrays(): Generator
    {
        yield 'empty_array_is_list' => [[], true];
        yield 'list_0' => [[2, 3, 4], true];
        yield 'list_1' => [['apple', 2, 3], true];
        yield 'list_2' => [[0 => 'apple', 'orange'], true];
        yield 'list_3' => [[0 => 'apple', "1" => 'orange'], true];
        yield 'list_4' => [['abc', 'def', 'ghi'], true];
        yield 'list_5' => [['abc', ['a' => 1, 'b' => 2], 'ghi'], true];
        yield 'does_not_start_at_zero' => [[1 => 'apple', 'orange'], false];
        yield 'not_in_order' => [[1 => 'apple', 0 => 'orange'], false];
        yield 'mixed_keys' => [[0 => 'apple', 'foo' => 'bar'], false];
        yield 'non-sequential' => [[0 => 'apple', 2 => 'bar'], false];
    }

    #[DataProvider('providesConvertNestedObjectsTests')]
    #[Test]
    public function convertNestedObjects_converts_nested_objects(mixed $test, array $expected): void
    {
        self::assertSame($expected, Arr::convertNestedObjects($test));
    }

    public static function providesConvertNestedObjectsTests(): \Generator
    {
        yield ['', ['']];
        yield [null, [null]];
        yield [new stdClass(), []];
        yield [[new stdClass()], [[]]];

        $object = new stdClass();
        $object->foo = 1;
        $object->bar = new stdClass();
        $object->bar->baz = new stdClass();
        yield [$object, ['foo' => 1, 'bar' => ['baz' => []]]];

        yield [new NestedObject('foo', 'bar'), ['public_value' => 'foo']];

        require_once __DIR__ . '/Fixture/NestingObject.php';
        require_once __DIR__ . '/Fixture/NestedObject.php';

        $test_object = new NestingObject(
            new NestedObject(1, 2),
            new NestedObject(1, 2),
            new NestedObject('foo1', 'bar1'),
            new NestedObject('foo2', 'bar2'),
            new NestedObject('foo3', 'bar3'),
        );

        $test_array = [
            'foo' => 42,
            'nested_object_array' => [
                ['public_value' => 'foo1'],
                ['public_value' => 'foo2'],
                ['public_value' => 'foo3'],
            ],
            'public_nested_object' => ['public_value' => 1],
        ];

        yield [$test_object, $test_array];
        yield [$test_array, $test_array];

        yield [\NAN, []];
        yield [\INF, []];
    }
}
