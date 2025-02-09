<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\Result;

use LogicException;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\RouteNotFound as SUT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RouteNotFoundTest extends TestCase
{
    #[Test]
    public function make_returns_found(): void
    {
        $sut = SUT::make();
        self::assertFalse($sut->isFound());
    }

    #[Test]
    public function make_does_not_return_RouteMatch(): void
    {
        $sut = SUT::make();
        $this->expectException(LogicException::class);
        $sut->getRouteMatch();
    }
}
