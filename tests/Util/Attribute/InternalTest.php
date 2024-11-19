<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Attribute;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Internal;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InternalTest extends TestCase
{
    #[Test]
    public function happy_path(): void
    {
        $sut = new Internal();
        self::assertSame('', $sut->help);

        $sut = new Internal('This is a test');
        self::assertSame('This is a test', $sut->help);
    }
}
