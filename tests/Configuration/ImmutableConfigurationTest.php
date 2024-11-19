<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Configuration;

use PhoneBurner\SaltLite\Framework\Configuration\ImmutableConfiguration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ImmutableConfigurationTest extends TestCase
{
    private const array TEST_HAYSTACK = [
        'key.includes.dot' => 'key-includes-dot-value',
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
        'l1' => [
            'l2' => [
                'l3' => [
                    'l4' => [
                        'l5' => [
                            'l6' => [
                                'l7' => [
                                    'l8' => [
                                        'l9' => [
                                            'l10' => 'really-really-deep-value',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
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

    #[Test]
    public function all_values_are_accessible(): void
    {
        $sut = new ImmutableConfiguration(self::TEST_HAYSTACK);
        self::assertSame(self::TEST_HAYSTACK, $sut->values);
    }

    #[Test]
    #[DataProvider('providesGetAndHasTestCases')]
    public function get_traverses_array_by_dot_notation(string $needle, bool $exists, mixed $expected): void
    {
        $sut = new ImmutableConfiguration(self::TEST_HAYSTACK);
        self::assertSame($exists, $sut->has($needle));
        self::assertSame($expected, $sut->get($needle));
    }

    public static function providesGetAndHasTestCases(): \Generator
    {
        yield 'key.includes.dot' => ['key.includes.dot', true, 'key-includes-dot-value'];
        yield 'exists' => ['top_level_exists', true, 'foo'];
        yield 'false' => ['top_level_false', true, false];
        yield 'empty' => ['top_level_empty', true, []];
        yield 'null:default:null' => ['top_level_null', false, null];
        yield 'not_exists:default:null' => ['not_exists', false, null];
        yield 'not_exists_dot:default:null' => ['not_exists.not_exists', false, null];
        yield 'foo' => ['foo', true, ['bar' => "Hello, World!", 'baz' => ['foo' => 1234, 'bar' => true, 'baz' => false, 'qux' => null]]];
        yield 'foo.bar' => ['foo.bar', true, "Hello, World!"];
        yield 'foo.baz' => ['foo.baz', true, ['foo' => 1234, 'bar' => true, 'baz' => false, 'qux' => null]];
        yield 'foo.baz.foo' => ['foo.baz.foo', true, 1234, 'error'];
        yield 'foo.baz.bar' => ['foo.baz.bar', true, true, 'error'];
        yield 'foo.baz.baz' => ['foo.baz.baz', true, false, 'error'];
        yield 'foo.baz.qux' => ['foo.baz.qux', false, null];
        yield 'foo.baz.not' => ['foo.baz.not', false, null];
        yield 'foo.baz.not.nope' => ['foo.baz.not.nope', false, null];
        yield 'foo.baz.not.nope.nah' => ['foo.baz.not.nope.nah', false, null];
        yield 'foo.not.foo' => ['foo.not.foo', false, null];
        yield 'really-deep-value' => ['l1.l2.l3.l4.l5.l6.l7.l8.l9.l10', true, 'really-really-deep-value'];
        yield 'empty_parent' => ['empty', true, ['foo' => []]];
        yield 'empty_child' => ['empty.foo', true, []];
        yield 'dot_string' => ['.', true, [
            null => [
                '' => 'deep_hidden',
                'deep' => 'deep_value',
            ],
        ]];
        yield 'empty_string' => ['', true, [
            'hidden' => 'foobar_hidden',
            '' => [
                null => [
                    '' => 'deep_hidden',
                    'deep' => 'deep_value',
                ],
            ],
        ]];
        yield 'leading_dot_key' => ['.leading_dot', true, 'leading_dot_value'];
        yield 'leading_dot_0' => ['.hidden', true, 'foobar_hidden'];
        yield 'leading_dot_1' => ['...', true, 'deep_hidden'];
        yield 'leading_dot_2' => ['...deep', true, 'deep_value'];
        yield 'leading_dot_3' => ['.....', false, null];
        yield 'leading_dot_4' => ['....deeper', false, null];
    }
}
