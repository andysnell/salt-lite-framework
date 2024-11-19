<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache;

use PhoneBurner\SaltLite\Framework\Cache\CacheDriver;
use PhoneBurner\SaltLite\Framework\Cache\CacheKey;
use PhoneBurner\SaltLite\Framework\Cache\Lock\NamedKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase
{
    #[DataProvider('providesTestCases')]
    #[Test]
    public function make_normalizes_key(array $parts, string $expected): void
    {
        self::assertSame($expected, CacheKey::make(...$parts)->normalized);
    }

    public static function providesTestCases(): \Generator
    {
        yield [[0], '0'];
        yield [[1], '1'];
        yield [['0'], '0'];
        yield [['1'], '1'];
        yield [['user'], 'user'];
        yield [['FooBarProfile'], 'foo_bar_profile'];
        yield [['user', 1, 'FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['user', 0, 'FooBarProfile'], 'user.0.foo_bar_profile'];
        yield [['user', 1, 'FooBarProfile:42'], 'user.1.foo_bar_profile_42'];
        yield [['user.....', 1, 'FooBarProfile:42'], 'user.1.foo_bar_profile_42'];
        yield [[CacheDriver::Memory], 'memory'];
        yield [[CacheDriver::Memory, CacheDriver::File], 'memory.file'];

        yield [['test_value.42'], 'test_value.42'];
        yield [['user.1.foo_bar_profile'], 'user.1.foo_bar_profile'];
        yield [['user.1.FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['.user.1.FooBarProfile'], 'user.1.foo_bar_profile'];
        yield [['user.1.FooBarProfile.'], 'user.1.foo_bar_profile'];

        yield [['key'], 'key'];
        yield [['key_with_underscore'], 'key_with_underscore'];
        yield [['key.with.dots'], 'key.with.dots'];
        yield [['key with spaces'], 'key_with_spaces'];
        yield [['key:with:colons'], 'key_with_colons'];
        yield [['key{with}braces'], 'key_with_braces'];
        yield [['key(with)parens'], 'key_with_parens'];
        yield [['key/with/slashes'], 'key_with_slashes'];
        yield [['key@with@at'], 'key_with_at'];
        yield [['key\\with\\backslashes'], 'key_with_backslashes'];
        yield [['key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes'], 'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes'];
        yield [[CacheDriver::Remote, NamedKey::class . ':1234'], 'remote.phone_burner_salt_lite_framework_cache_lock_named_key_1234'];
        yield [[NamedKey::class . ':1234'], 'phone_burner_salt_lite_framework_cache_lock_named_key_1234'];

        yield [
            [new class implements \Stringable {
                public function __toString(): string
                {
                    return 'key with spaces:and:colons{and}braces(with)parens/and/slashes@and@at\\and\\backslashes';
                }
            }],
            'key_with_spaces_and_colons_and_braces_with_parens_and_slashes_and_at_and_backslashes',
        ];
    }

    #[DataProvider('providesEmptyStringTestCases')]
    #[Test]
    public function make_throws_exception_when_empty_string_passed(array $parts): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cache key part cannot be empty string');
        CacheKey::make(...$parts);
    }

    public static function providesEmptyStringTestCases(): \Generator
    {
        yield [['']];
        yield [['user', 1, '']];
        yield [['user', '', 'FooBarProfile']];
        yield [['', '1', 'FooBarProfile']];
    }
}
