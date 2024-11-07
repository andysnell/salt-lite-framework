<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Domain\Api;

use Laminas\Diactoros\Uri;
use PhoneBurner\LinkTortilla\Link;
use PhoneBurner\SaltLite\Framework\Domain\Api\HalResource;
use PhoneBurner\SaltLite\Framework\Util\Helper\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class HalResourceTest extends TestCase
{
    private function makeMockDataForSUT(array $overrides = []): array
    {
        $properties = $overrides['properties'] ?? [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $links = $overrides['links'] ?? [
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
        ];

        $embedded = $overrides['embedded'] ?? [
            'abc' => HalResource::make(['other' => 12345]),
            'def' => [
                HalResource::make(['foobar' => 'Test String 1']),
                HalResource::make(['bazqux' => 'Test String 2']),
            ],
        ];

        return [$properties, $links, $embedded];
    }

    private function makeSUT(array $overrides = []): HalResource
    {
        return HalResource::make(...$this->makeMockDataForSUT($overrides));
    }

    #[DataProvider('providesValidResourceDataLinksAndEmbeddedData')]
    #[Test]
    public function make_constructs_new_instance_of_HalResource(array $mock_data): void
    {
        [$properties, $links, $embedded] = $this->makeMockDataForSUT($mock_data);

        self::assertInstanceOf(HalResource::class, HalResource::make($properties, $links, $embedded));
    }

    public static function providesValidResourceDataLinksAndEmbeddedData(): \Generator
    {
        yield 'base_test' => [[]];

        yield 'properties_can_be_iterable' => [['properties' => new \ArrayObject([
            'foo' => 'bar',
            'bar' => 'baz',
        ])]];

        yield 'links_can_be_iterable' => [['links' => new \ArrayObject([
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
        ])]];

        yield 'embedded_resources_can_be_iterable' => [['embedded' => new \ArrayObject([
            'ham' => HalResource::make(['foo' => 'bar', 'bar' => 'baz']),
            'spam' => HalResource::make(['foo' => 'bar', 'bar' => 'baz']),
            'eggs' => HalResource::make(),
        ])]];

        yield 'embedded_resources_can_be_array_of_HalResource' => [['embedded' => [
            'ham' => HalResource::make(['foo' => 'bar', 'bar' => 'baz']),
            'spam' => [
                HalResource::make(['foo' => '1']),
                HalResource::make(['foo' => '2']),
                HalResource::make(['foo' => '3']),
            ],
        ]]];
    }

    #[DataProvider('providesInvalidResourceDataLinksAndEmbeddedData')]
    #[Test]
    public function make_validates_and_filters_the_data_passed(array $mock_data): void
    {
        [$properties, $links, $embedded] = $this->makeMockDataForSUT($mock_data);

        $this->expectException(\InvalidArgumentException::class);
        HalResource::make($properties, $links, $embedded);
    }

    public static function providesInvalidResourceDataLinksAndEmbeddedData(): \Generator
    {
        foreach ([0, 1, true, false, null, ''] as $key => $name) {
            yield 'resource_property_name_must_be_non_empty_string_' . $key => [['properties' => [
                $name => 'foo',
            ],],];
        }

        yield from static::providesInvalidPropertyNamesAndValues();

        yield 'links_must_be_LinkInterfaces_0' => [['links' => [
            'https://example.com',
        ],],];

        yield 'links_must_be_LinkInterfaces_1' => [['links' => [
            Link::make('self', 'https://example.com'),
            new Uri('https://example.com'),
            Link::make('next', 'https://example.com/next'),
        ],],];

        foreach ([0, 1, true, false, null, ''] as $key => $name) {
            yield 'embedded_property_name_must_be_non_empty_string_' . $key => [['embedded' => [
                $name => HalResource::make(),
            ],],];
        }

        yield from static::providesInvalidEmbeddedPropertyNamesAndValues();
    }

    #[Test]
    public function make_handles_the_empty_case(): void
    {
        $empty_resource = HalResource::make();
        self::assertSame([], $empty_resource->getProperties());
        self::assertSame([], $empty_resource->getLinks());
        self::assertSame([], $empty_resource->getEmbeddedResources());
        self::assertSame([], $empty_resource->jsonSerialize());
    }

    #[TestWith(['foo', true])]
    #[TestWith(['qux', false])]
    #[TestWith(['foobar', true])]
    #[Test]
    public function hasProperty_returns_true_if_property_is_defined(string $name, bool $expected): void
    {
        self::assertSame($expected, $this->makeSUT(['properties' => [
            'foo' => 'bar',
            'bar' => 'baz',
            'foobar' => null,
        ],])->hasProperty($name));
    }

    #[Test]
    public function getProperty_returns_expected_property(): void
    {
        self::assertSame('bar', $this->makeSUT()->getProperty('foo'));
    }

    #[Test]
    public function getProperty_throws_exception_when_property_not_defined(): void
    {
        $sut = $this->makeSUT();

        $this->expectException(\LogicException::class);
        $sut->getProperty('qux');
    }

    #[Test]
    public function getProperties_returns_expected_properties_as_array(): void
    {
        self::assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $this->makeSUT()->getProperties());
    }

    #[Test]
    public function withProperty_returns_new_instance_with_property(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withProperty('qux', 'Hello, World');

        self::assertNotEquals($sut, $resource);
        self::assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
            'qux' => 'Hello, World',
        ], $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[DataProvider('providesInvalidPropertyNamesAndValues')]
    #[Test]
    public function withProperty_filters_the_parameters_passed(array $mock_data): void
    {
        $sut = $this->makeSUT();
        [$properties, $links, $embedded] = $this->makeMockDataForSUT($mock_data);

        $this->expectException(\InvalidArgumentException::class);
        foreach ($properties as $name => $property) {
            $sut->withProperty($name, $property);
        }
    }

    public static function providesInvalidPropertyNamesAndValues(): \Generator
    {
        yield 'using_closure_as_property' => [['properties' => [
            'foo' => static fn(): string => 'closures cannot be sanely serialized by PHP into JSON',
        ],],];

        yield 'using_resource_as_property' => [['properties' => [
            'foo' => Str::stream('resources cannot be sanely serialized by PHP into JSON')->detach(),
        ],],];
    }

    #[Test]
    public function withoutProperty_returns_new_instance_without_property(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withoutProperty('foo');

        self::assertNotEquals($sut, $resource);
        self::assertSame([
            'bar' => 'baz',
        ], $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[Test]
    public function withoutProperty_returns_new_instance_if_property_not_defined(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withoutProperty('qux');

        self::assertNotSame($sut, $resource);
        self::assertSame([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[Test]
    public function getLinks_returns_links_as_array(): void
    {
        self::assertEqualsCanonicalizing([
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
        ], $this->makeSUT()->getLinks());
    }

    #[Test]
    public function getLinksByRel_returns_links_filtered_by_rel(): void
    {
        $sut = $this->makeSUT();

        self::assertEqualsCanonicalizing([
            Link::make('self', 'https://example.com/foo/1234'),
        ], $sut->getLinksByRel('self'));

        self::assertEqualsCanonicalizing([
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
        ], $sut->getLinksByRel('docs'));
    }

    #[Test]
    public function withLink_returns_new_instance_with_link(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withLink(Link::make('foobar', 'https://example.com/foobar'));

        self::assertNotEquals($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEqualsCanonicalizing([
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
            Link::make('foobar', 'https://example.com/foobar'),
        ], $resource->getLinks());
        self::assertSame($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[Test]
    public function withLink_does_not_add_the_same_link_object_twice(): void
    {
        $test_link = Link::make('foobar', 'https://example.com/foobar');
        $sut = $this->makeSUT(['links' => [
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
            $test_link,
        ],]);

        self::assertEqualsCanonicalizing([
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
            Link::make('foobar', 'https://example.com/foobar'),
        ], $sut->getLinks());

        $resource = $sut->withLink($test_link);

        self::assertNotSame($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEqualsCanonicalizing($sut->getLinks(), $resource->getLinks());
        self::assertSame($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[Test]
    public function withoutLink_removes_link_and_returns_new_instance(): void
    {
        $sut = $this->makeSUT();
        $links = $sut->getLinks();
        $resource = $sut->withoutLink(\end($links) ?: self::fail('No links found'));

        self::assertNotEquals($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEqualsCanonicalizing([
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
        ], $resource->getLinks());
        self::assertSame($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[TestWith(['abc', true])]
    #[TestWith(['def', true])]
    #[TestWith(['qux', false])]
    #[Test]
    public function hasEmbeddedResource_returns_true_if_property_is_defined(string $name, bool $expected): void
    {
        self::assertSame($expected, $this->makeSUT()->hasEmbeddedResource($name));
    }

    #[Test]
    public function getEmbeddedResource_returns_expected_Resource_or_array(): void
    {
        $sut = $this->makeSUT();

        self::assertEquals(HalResource::make(['other' => 12345]), $sut->getEmbeddedResource('abc'));
        self::assertEquals([
            HalResource::make(['foobar' => 'Test String 1']),
            HalResource::make(['bazqux' => 'Test String 2']),
        ], $sut->getEmbeddedResource('def'));
    }

    #[Test]
    public function getEmbeddedResource_throws_exception_when_property_not_defined(): void
    {
        $sut = $this->makeSUT();

        $this->expectException(\LogicException::class);
        $sut->getEmbeddedResource('qux');
    }

    #[Test]
    public function getEmbeddedResources_returns_expected_resources_as_array(): void
    {
        self::assertEquals([
            'abc' => HalResource::make(['other' => 12345]),
            'def' => [
                HalResource::make(['foobar' => 'Test String 1']),
                HalResource::make(['bazqux' => 'Test String 2']),
            ],
        ], $this->makeSUT()->getEmbeddedResources());
    }

    #[Test]
    public function withEmbedded_returns_new_instance_with_property(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withEmbeddedResource('ghi', HalResource::make(['foo' => 'foobar']));

        self::assertNotEquals($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals([
            'abc' => HalResource::make(['other' => 12345]),
            'def' => [
                HalResource::make(['foobar' => 'Test String 1']),
                HalResource::make(['bazqux' => 'Test String 2']),
            ],
            'ghi' => HalResource::make(['foo' => 'foobar']),
        ], $resource->getEmbeddedResources());
    }

    #[DataProvider('providesInvalidEmbeddedPropertyNamesAndValues')]
    #[Test]
    public function withEmbeddedResource_fiters_the_parameters_passed(array $mock_data): void
    {
        $sut = $this->makeSUT();
        [$properties, $links, $embedded] = $this->makeMockDataForSUT($mock_data);

        $this->expectException(\InvalidArgumentException::class);
        foreach ($embedded as $name => $resource) {
            $sut->withEmbeddedResource($name, $resource);
        }
    }

    public static function providesInvalidEmbeddedPropertyNamesAndValues(): \Generator
    {
        $invalid_embedded_resources = [
            'class' => new \stdClass(),
            'link' => Link::make('baz', 'https://example.com'),
        ];

        foreach ($invalid_embedded_resources as $type => $resource) {
            yield 'embedded_cannot_be_a_' . $type => [['embedded' => [
                'foo' => $resource,
            ],],];
        }
    }

    #[Test]
    public function withoutEmbedded_returns_new_instance_without_property(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withoutEmbeddedResource('def');

        self::assertNotEquals($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals([
            'abc' => HalResource::make(['other' => 12345]),
        ], $resource->getEmbeddedResources());
    }

    #[Test]
    public function withoutEmbedded_returns_new_instance_if_property_not_defined(): void
    {
        $sut = $this->makeSUT();
        $resource = $sut->withoutEmbeddedResource('ghi');

        self::assertNotSame($sut, $resource);
        self::assertEquals($sut->getProperties(), $resource->getProperties());
        self::assertEquals($sut->getLinks(), $resource->getLinks());
        self::assertEquals($sut->getEmbeddedResources(), $resource->getEmbeddedResources());
    }

    #[Test]
    public function links_are_arrayable(): void
    {
        $expected_json = '{"_links":{"test_relation":[{"href":"\/api\/rest\/test\/1234"}]}}';

        $sut = HalResource::make([], [
            Link::make('test_relation', '/api/rest/test/1234')->asArray(),
        ]);

        self::assertEquals($expected_json, \json_encode($sut, \JSON_THROW_ON_ERROR));
    }

    #[Test]
    public function jsonSerialize_handles_normal_links(): void
    {
        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
            '_links' => [
                'self' => [
                    'href' => 'https://example.com/foo/1234',
                ],
                'docs' => [
                    ['href' => 'https://example.com/docs'],
                    ['href' => 'https://example.com/docs/extra'],
                ],
            ],
            '_embedded' => [
                'abc' => HalResource::make(['other' => 12345]),
                'def' => [
                    HalResource::make(['foobar' => 'Test String 1']),
                    HalResource::make(['bazqux' => 'Test String 2']),
                ],
            ],
        ];

        self::assertEquals($expected, $this->makeSUT()->jsonSerialize());
    }

    #[Test]
    public function jsonSerialize_handles_templated_links_with_attributes(): void
    {
        $sut = $this->makeSUT(['links' => [
            Link::make('self', 'https://example.com/foo/1234'),
            Link::make('docs', 'https://example.com/docs'),
            Link::make('docs', 'https://example.com/docs/extra'),
            Link::make('find', 'https://example.com/foo/{foo}'),
            Link::make('next', 'https://example.com/foo/next')->withAttribute('title', 'this is an attribute'),
            Link::make('prev', 'https://example.com/foo/{prev}')->withAttribute('title', 'this is an attribute'),
        ],]);

        $expected = [
            'foo' => 'bar',
            'bar' => 'baz',
            '_links' => [
                'self' => [
                    'href' => 'https://example.com/foo/1234',
                ],
                'docs' => [
                    ['href' => 'https://example.com/docs'],
                    ['href' => 'https://example.com/docs/extra'],
                ],
                'find' => [
                    'href' => 'https://example.com/foo/{foo}',
                    'templated' => true,
                ],
                'next' => [
                    'href' => 'https://example.com/foo/next',
                    'title' => 'this is an attribute',
                ],
                'prev' => [
                    'href' => 'https://example.com/foo/{prev}',
                    'templated' => true,
                    'title' => 'this is an attribute',
                ],
            ],
            '_embedded' => [
                'abc' => HalResource::make(['other' => 12345]),
                'def' => [
                    HalResource::make(['foobar' => 'Test String 1']),
                    HalResource::make(['bazqux' => 'Test String 2']),
                ],
            ],
        ];

        self::assertEquals($expected, $sut->jsonSerialize());
    }
}
