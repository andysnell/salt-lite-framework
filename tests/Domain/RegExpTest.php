<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain;

use PhoneBurner\SaltLite\Framework\Domain\RegExp;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RegExpTest extends TestCase
{
    #[DataProvider('regular_expressions')]
    #[Test]
    public function make_returns_expected(string $regexp, string $modifiers, string $expected): void
    {
        self::assertSame($expected, (string)RegExp::make($regexp, $modifiers));
    }

    public static function regular_expressions(): \Generator
    {
        yield ['[Aa]', '', '/[Aa]/'];
        yield ['[Aa]', 'i', '/[Aa]/i'];
        yield ['[Aa]', 'ig', '/[Aa]/ig'];
    }
}
