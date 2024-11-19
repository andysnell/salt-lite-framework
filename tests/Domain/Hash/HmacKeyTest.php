<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Hash;

use PhoneBurner\SaltLite\Framework\Domain\Hash\HmacKey;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HmacKeyTest extends TestCase
{
    private const string VALID_KEY_STRING = 'e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045f';

    #[Test]
    #[DataProvider('providesValidKeyValues')]
    public function make_creates_valid_key(string|\Stringable $value): void
    {
        $key = HmacKey::make($value);
        self::assertSame(self::VALID_KEY_STRING, (string)$key);
        self::assertSame(self::VALID_KEY_STRING, $key->value);
    }

    #[Test]
    #[DataProvider('providesInvalidKeyValues')]
    public function make_creates_invalid_key(string|\Stringable $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HmacKey::make($value);
    }

    #[Test]
    public function generate_creates_valid_key(): void
    {
        $key = HmacKey::generate();
        self::assertSame((string)$key, $key->value);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string)$key);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $key->value);
    }

    public static function providesValidKeyValues(): \Generator
    {
        yield ['e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045f'];
        yield ['E03518C3BC776D427DE90C477FDE1A23FA5103237A13D095089B9EA0EC11045F'];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return 'e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045f';
            }
        }];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return 'E03518C3BC776D427DE90C477FDE1A23FA5103237A13D095089B9EA0EC11045F';
            }
        }];
    }

    public static function providesInvalidKeyValues(): \Generator
    {
        yield [''];
        yield ['e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045'];
        yield ['e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045ff'];
        yield ['E03518C3BC776D427DE90C477FDE1A23FA5103237A13D095089B9EA0EC11045Z'];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return '';
            }
        }];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return 'e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045';
            }
        }];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return 'e03518c3bc776d427de90c477fde1a23fa5103237a13d095089b9ea0ec11045ff';
            }
        }];
        yield [new class implements \Stringable {
            public function __toString(): string
            {
                return 'E03518C3BC776D427DE90C477FDE1A23FA5103237A13D095089B9EA0EC11045Z';
            }
        }];
    }
}
