<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\PhoneNumber\AreaCode;

use PhoneBurner\SaltLite\Framework\Domain\I18n\SubdivisionCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCode;
use PhoneBurner\SaltLite\Framework\Domain\PhoneNumber\AreaCode\AreaCodeCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AreaCodeCollectionTest extends TestCase
{
    #[Test]
    public function collects_area_codes(): void
    {
        $area_codes = [
            AreaCode::make(330),
            AreaCode::make(314),
            AreaCode::make(888),
        ];

        $collection = new AreaCodeCollection(...$area_codes);

        self::assertCount(3, $collection);
        self::assertSame($area_codes, [...$collection]);
        self::assertSame([
            330 => AreaCode::make(330),
            314 => AreaCode::make(314),
            888 => AreaCode::make(888),
        ], $collection->toArray());

        self::assertTrue($collection->contains(AreaCode::make(330)));
        self::assertTrue($collection->contains(AreaCode::make(314)));
        self::assertTrue($collection->contains(AreaCode::make(888)));
        self::assertFalse($collection->contains(AreaCode::make(216)));
        self::assertFalse($collection->contains(AreaCode::make(484)));

        $filtered = $collection->filter(fn(AreaCode $area_code): bool => \in_array(SubdivisionCode::US_MO, $area_code->location->subdivisions, true));

        self::assertCount(1, $filtered);
        self::assertTrue($filtered->contains(AreaCode::make(314)));
    }
}
