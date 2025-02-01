<?php

declare(strict_types=1);

namespace Http\Routing\Result;

use LogicException;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\Result\MethodNotAllowed as SUT;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MethodNotAllowedTest extends TestCase
{
    private array $methods;

    #[\Override]
    protected function setUp(): void
    {
        $this->methods = [HttpMethod::Post, HttpMethod::Put];
    }

    #[Test]
    public function make_returns_found(): void
    {
        $sut = SUT::make(...$this->methods);
        self::assertFalse($sut->isFound());
    }

    #[Test]
    public function make_does_not_return_RouteMatch(): void
    {
        $sut = SUT::make(...$this->methods);
        $this->expectException(LogicException::class);
        $sut->getRouteMatch();
    }
}
