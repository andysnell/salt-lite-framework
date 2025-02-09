<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\FastRoute;

use FastRoute\Dispatcher;
use Generator;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\FastRoute\FastRouteMatch as SUT;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class FastRouteMatchTest extends TestCase
{
    #[DataProvider('provideInvalidMatch')]
    #[Test]
    public function make_throws_UnexpectedValueException(array $match): void
    {
        $this->expectException(UnexpectedValueException::class);
        SUT::make($match);
    }

    #[Test]
    public function make_not_found_returns_expected_data(): void
    {
        $sut = SUT::make([
            Dispatcher::NOT_FOUND,
        ]);

        self::assertSame(Dispatcher::NOT_FOUND, $sut->getStatus());
        self::assertSame([], $sut->getMethods());
        self::assertSame([], $sut->getPathVars());
        self::assertSame('', $sut->getRouteData());
    }

    #[Test]
    public function make_found_returns_expected_data(): void
    {
        $sut = SUT::make([
            Dispatcher::FOUND,
            'serialized data',
            ['path' => 'data'],
        ]);

        self::assertSame(Dispatcher::FOUND, $sut->getStatus());
        self::assertSame([], $sut->getMethods());
        self::assertSame(['path' => 'data'], $sut->getPathVars());
        self::assertSame('serialized data', $sut->getRouteData());
    }

    #[Test]
    public function make_method_not_allowed_returns_expected_data(): void
    {
        $sut = SUT::make([
            Dispatcher::METHOD_NOT_ALLOWED,
            [HttpMethod::Get, HttpMethod::Post],
        ]);

        self::assertSame(Dispatcher::METHOD_NOT_ALLOWED, $sut->getStatus());
        self::assertSame([HttpMethod::Get, HttpMethod::Post], $sut->getMethods());
        self::assertSame([], $sut->getPathVars());
        self::assertSame('', $sut->getRouteData());
    }

    public static function provideInvalidMatch(): Generator
    {
        yield [[
            Dispatcher::METHOD_NOT_ALLOWED,
        ],];

        yield [[
            Dispatcher::METHOD_NOT_ALLOWED,
            HttpMethod::Get,
        ],];

        yield [[
            Dispatcher::FOUND,
        ],];

        yield [[
            Dispatcher::FOUND,
            ['data'],
        ],];

        yield [[
            Dispatcher::FOUND,
            ['data'],
            [],
        ],];

        yield [[
            Dispatcher::FOUND,
            'data',
        ],];

        yield [[
            Dispatcher::FOUND,
            'data',
            'string',
        ],];
    }
}
