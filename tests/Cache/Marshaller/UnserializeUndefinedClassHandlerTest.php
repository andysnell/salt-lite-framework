<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Cache\Marshaller;

use PhoneBurner\SaltLite\Framework\Cache\Marshaller\UnserializeUndefinedClassHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UnserializeUndefinedClassHandlerTest extends TestCase
{
    #[Test]
    public function handleUndefinedClass_throws_exception(): void
    {
        $this->expectException(\DomainException::class);
        UnserializeUndefinedClassHandler::fail('foo');
    }
}
