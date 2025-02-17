<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Exception;

use PhoneBurner\SaltLite\Framework\Util\Encoding;
use PhoneBurner\SaltLite\Framework\Util\Exception\InvalidStringableOffset;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidStringableOffsetTest extends TestCase
{
    #[Test]
    #[DataProvider('exceptionMessageProvider')]
    public function happy_path(mixed $offset, string $message): void
    {
        $exception = new InvalidStringableOffset($offset);
        self::assertSame($message, $exception->getMessage());
    }

    public static function exceptionMessageProvider(): \Iterator
    {
        yield [true, 'Invalid offset type: bool. Expected a stringable type.'];
        yield [\M_PI, 'Invalid offset type: float. Expected a stringable type.'];
        yield [Encoding::Base64, 'Invalid offset type: PhoneBurner\SaltLite\Framework\Util\Encoding. Expected a stringable type.'];
    }
}
