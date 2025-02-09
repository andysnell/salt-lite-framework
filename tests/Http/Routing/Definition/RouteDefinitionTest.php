<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Http\Routing\Definition;

use ArrayIterator;
use Generator;
use InvalidArgumentException;
use PhoneBurner\SaltLite\Framework\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition as SUT;
use PhoneBurner\SaltLite\Framework\Http\Routing\Domain\StaticFile;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\RedirectRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\StaticFileRequestHandler;
use PhoneBurner\SaltLite\Framework\Http\Routing\Route;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\TestRequestHandler;
use PhoneBurner\SaltLite\Framework\Util\Helper\Arr;
use PhoneBurner\SaltLite\Framework\Util\Helper\Enum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;
use TypeError;

final class RouteDefinitionTest extends TestCase
{
    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function make_returns_RouteDefinition_with_expected_values(array $test_case, array $methods): void
    {
        $sut = SUT::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[Test]
    public function make_returns_RouteDefinition_with_unique_methods(): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get, HttpMethod::Get, HttpMethod::Put, HttpMethod::Post, HttpMethod::Post],
        );

        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value, HttpMethod::Put->value, HttpMethod::Post->value], $sut->getMethods());
        self::assertSame([], $sut->getAttributes());
    }

    #[TestWith(['just a string'])]
    #[TestWith(['\stdClass'])]
    #[Test]
    public function make_requires_handler_to_implement_interface_if_provided(string $class): void
    {
        $this->expectException(InvalidArgumentException::class);

        SUT::make(
            '/example',
            [HttpMethod::Get],
            [
                RequestHandlerInterface::class => $class,
            ],
        );
    }

    #[DataProvider('provideRouteNames')]
    #[Test]
    public function make_requires_valid_name_if_provided(string $name, bool $valid): void
    {
        if (! $valid) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        SUT::make(
            '/example',
            [HttpMethod::Get],
            [
                Route::class => $name,
            ],
        );
    }

    #[DataProvider('provideTestDataWithNamedConstructors')]
    #[Test]
    public function named_constructors_return_RouteDefinition_with_expected_values(
        array $test_case,
        string $method,
        array $methods,
    ): void {
        $sut = SUT::$method(
            $test_case['path'],
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertSame($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function serialization_maintains_state(array $test_case, array $methods): void
    {
        $sut = SUT::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $sut = \unserialize(\serialize($sut));

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertSame($test_case['expected_attributes'], $sut->getAttributes());
    }

    #[Test]
    public function serialization_sets_wrapped_Uri(): void
    {
        $sut = SUT::get('/test');

        self::assertSame('/test', $sut->getPath());

        $sut = \unserialize(\serialize($sut));

        self::assertSame('/test', $sut->getPath());
    }

    #[Test]
    public function getRoutePath_returns_path(): void
    {
        $sut = SUT::make('/test_path', [HttpMethod::Get]);

        self::assertSame('/test_path', $sut->getRoutePath());
    }

    #[Test]
    public function getAttributes_returns_attributes(): void
    {
        $attributes = [
            'test_attribute' => 'test_value',
            'test_attribute2' => 'test_value2',
        ];

        $sut = SUT::make('/test_path', [HttpMethod::Get], $attributes);

        self::assertSame($attributes, $sut->getAttributes());
    }

    #[Test]
    public function getMethods_returns_methods(): void
    {
        $methods = [HttpMethod::Get, HttpMethod::Patch];
        $sut = SUT::make('/test_path', $methods);

        self::assertSame(
            \array_map(static fn(HttpMethod $method): string => $method->value, $methods),
            $sut->getMethods(),
        );
    }

    #[Test]
    public function with_passes_self_to_methods_and_returns(): void
    {
        $sut = SUT::make('/test');

        $first = SUT::make('/first');
        $second = SUT::make('/second');
        $final = SUT::make('/final');

        self::assertSame(
            $final,
            $sut->with(static function (SUT $actual) use ($sut, $first): SUT {
                self::assertSame($actual, $sut);
                return $first;
            }, static function (SUT $actual) use ($first, $second): SUT {
                self::assertSame($first, $actual);
                return $second;
            }, static function (SUT $actual) use ($second, $final): SUT {
                self::assertSame($second, $actual);
                return $final;
            }),
        );
    }

    #[Test]
    public function with_rejects_any_none_self_return(): void
    {
        $sut = SUT::make('/test');

        $this->expectException(TypeError::class);
        $sut->with(static fn(): SUT => $sut, static fn(): stdClass => new stdClass());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function withPathParameter_maintains_state(array $test_case, array $methods): void
    {
        $sut = SUT::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $new = $sut->withPathParameter('test', 'value');
        self::assertNotSame($sut, $new);

        self::assertSame($test_case['path'], $new->getRoutePath());
        self::assertEquals($methods, $new->getMethods());
        self::assertEquals($test_case['expected_attributes'], $new->getAttributes());
    }

    #[DataProvider('provideTestDataWithMethod')]
    #[Test]
    public function withPathParameters_maintains_state(array $test_case, array $methods): void
    {
        $sut = SUT::make(
            $test_case['path'],
            $methods,
            $test_case['attributes'],
        );

        self::assertSame($test_case['path'], $sut->getRoutePath());
        self::assertEquals($methods, $sut->getMethods());
        self::assertEquals($test_case['expected_attributes'], $sut->getAttributes());

        $new = $sut->withPathParameters([
            'test' => 'data',
        ]);
        self::assertNotSame($sut, $new);

        self::assertSame($test_case['path'], $new->getRoutePath());
        self::assertEquals($methods, $new->getMethods());
        self::assertEquals($test_case['expected_attributes'], $new->getAttributes());
    }

    #[Test]
    public function withAttribute_adds_attribute(): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data1'],
        );

        $new = $sut->withAttribute('new', 'data2');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame([
            'old' => 'data1',
            'new' => 'data2',
        ], $new->getAttributes());
    }

    #[Test]
    public function withAttribute_replaces_attribute(): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            [
                'old' => 'data1',
                'replace' => 'old data',
            ],
        );

        $new = $sut->withAttribute('replace', 'new data');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame([
            'old' => 'data1',
            'replace' => 'old data',
        ], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame([
            'old' => 'data1',
            'replace' => 'new data',
        ], $new->getAttributes());
    }

    #[Test]
    public function withAttributes_replaces_attribute_array(): void
    {
        $old_attributes = [
            'old' => 'data1',
            'replace' => 'old data',
        ];

        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            $old_attributes,
        );

        $new_attributes = [
            'totally' => 'new',
            'set' => 'of data',
        ];

        $new = $sut->withAttributes($new_attributes);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame($old_attributes, $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertSame($new_attributes, $new->getAttributes());
    }

    #[Test]
    public function withAddedAttributes_merged_attribute_array(): void
    {
        $old_attributes = [
            'old' => 'data1',
            'replace' => 'old data',
        ];

        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            $old_attributes,
        );

        $new_attributes = [
            'totally' => 'new',
            'set' => 'of data',
            'replace' => 'new data',
        ];

        $new = $sut->withAddedAttributes($new_attributes);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame($old_attributes, $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $new->getMethods());
        self::assertEqualsCanonicalizing([
            'old' => 'data1',
            'totally' => 'new',
            'set' => 'of data',
            'replace' => 'new data',
        ], $new->getAttributes());
    }

    #[DataProvider('provideChangedMethods')]
    #[Test]
    public function withMethod_replaces_method(array $old_methods, array $new_methods, array $args): void
    {
        $sut = SUT::make(
            '/example',
            $old_methods,
            ['old' => 'data1'],
        );

        $new = $sut->withMethod(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertEquals(Enum::values(...$old_methods), $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertEqualsCanonicalizing(Enum::values(...$new_methods), $new->getMethods());
        self::assertSame(['old' => 'data1'], $new->getAttributes());
    }

    #[DataProvider('provideAddedMethods')]
    #[Test]
    public function withAddedMethod_adds_method(array $old_methods, array $new_methods, array $args): void
    {
        $sut = SUT::make(
            '/example',
            $old_methods,
            ['old' => 'data1'],
        );

        $new = $sut->withAddedMethod(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertEquals(\array_column($old_methods, 'value'), $sut->getMethods());
        self::assertSame(['old' => 'data1'], $sut->getAttributes());

        // changed
        self::assertSame('/example', $new->getRoutePath());
        self::assertEqualsCanonicalizing(\array_column($new_methods, 'value'), $new->getMethods());
        self::assertSame(['old' => 'data1'], $new->getAttributes());
    }

    #[Test]
    public function withRoutePath_replaces_path(): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data'],
        );

        $new = $sut->withRoutePath('/new');
        self::assertNotSame($sut, $new);

        // not changed
        self::assertSame('/example', $sut->getRoutePath());
        self::assertSame([HttpMethod::Get->value], $sut->getMethods());
        self::assertSame(['old' => 'data'], $sut->getAttributes());

        // changed
        self::assertSame('/new', $new->getRoutePath());
        self::assertEqualsCanonicalizing([HttpMethod::Get->value], $new->getMethods());
        self::assertSame(['old' => 'data'], $new->getAttributes());
    }

    #[DataProvider('provideNamedSettersAndValues')]
    #[Test]
    public function setters_add_named_attribute(string $method, string $property, array $args, mixed $value): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            ['old' => 'data'],
        );

        $new = $sut->$method(...$args);
        self::assertNotSame($sut, $new);

        // not changed
        self::assertEqualsCanonicalizing([
            'path' => '/example',
            'methods' => [HttpMethod::Get->value],
            'attributes' => ['old' => 'data'],
        ], $sut->__serialize());

        // changed
        self::assertEqualsCanonicalizing([
            'path' => '/example',
            'methods' => [HttpMethod::Get->value],
            'attributes' => [
                'old' => 'data',
                $property => $value,
            ],
        ], $new->__serialize());
    }

    #[DataProvider('provideRouteNames')]
    #[Test]
    public function withName_requires_valid_name(string $name, bool $valid): void
    {
        if (! $valid) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        SUT::make('/example', [HttpMethod::Get])->withName($name);
    }

    #[TestWith(['just a string'])]
    #[TestWith(['\stdClass'])]
    #[Test]
    public function withHandler_requires_handler_class(string $class): void
    {
        $sut = SUT::make(
            '/example',
            [HttpMethod::Get],
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore-next-line Intentional Defect for Testing Sad Path */
        $sut->withHandler($class);
    }

    #[DataProvider('provideUriTestCase')]
    #[Test]
    public function wrapped_Uri_has_expected_path(array $test_case): void
    {
        $sut = SUT::make(
            $test_case['path'],
            [HttpMethod::Get],
            [],
        );

        self::assertSame($test_case['uri_path'], (string)$sut);

        if ($test_case['templated_path']) {
            $uri = $sut;
            foreach ($test_case['template'] as [$method, $args]) {
                $uri = $uri->$method(...$args);
            }

            self::assertSame($test_case['templated_path'], (string)$uri);
        }
    }

    public static function provideRouteNames(): Generator
    {
        $special = \str_split('\-_.');

        $words = ['test2', 'name1'];

        foreach ($special as $character) {
            $name = \implode($character, $words);
            yield $name => [$name, true];

            $name = \ucwords($name);
            yield $name => [$name, true];

            $name = \strtoupper($name);
            yield $name => [$name, true];

            $name = "\\" . $name;

            yield $name => [$name, true];
        }

        $bad_start = \str_split('1234567890-_.');

        foreach ($bad_start as $character) {
            $name = $character . 'test-name';
            yield $name => [$name, false];
        }

        $bad = ['💩', ...\str_split('!@#$%^&*()+[]{}:<>/|?')];

        foreach ($bad as $character) {
            $name = \implode($character, $words);
            yield $name => [$name, false];
        }
    }

    public static function provideUriTestCase(): Generator
    {
        yield 'no vars' => [
            [
                'path' => '/test',
                'uri_path' => '/test',
                'templated_path' => '/test',
                'template' => [
                    ['withPathParameter', ['any', 'data']],
                ],
            ],
        ];

        $patterns = ['', ':\d+', ':(?:en|de)'];

        foreach ($patterns as $pattern) {
            $test_case = [
                'path' => '/test/{var' . $pattern . '}',
                'uri_path' => '/test/',
                'templated_path' => '/test/value',
            ];

            yield 'single var with param: var' . $pattern => [
                [...$test_case, 'template' => [
                    ['withPathParameter', ['var', 'value']],
                ]],
            ];

            yield 'single var with params: var' . $pattern => [
                [...$test_case, 'template' => [
                    ['withPathParameters', [['var' => 'value']]],
                ]],
            ];

            $test_case = [
                'path' => "/test/{var1{$pattern}}/path/{var2{$pattern}}",
                'uri_path' => '/test//path/',
                'templated_path' => '/test/value',
            ];

            yield 'multiple var first with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value/path/',
                    'template' => [
                        ['withPathParameter', ['var1', 'value']],
                    ],
                ],
            ];

            yield 'multiple var second with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test//path/value',
                    'template' => [
                        ['withPathParameter', ['var2', 'value']],
                    ],
                ],
            ];

            yield 'multiple var both with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        ['withPathParameter', ['var2', 'value2']],
                        ['withPathParameter', ['var1', 'value1']],
                    ],
                ],
            ];

            yield 'multiple var both with params using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        [
                            'withPathParameters', [
                            [
                                'var1' => 'value1',
                                'var2' => 'value2',
                            ],
                            ],
                        ],
                    ],
                ],
            ];

            $test_case = [
                'path' => "/test/[{var1{$pattern}}/]path/{var2{$pattern}}",
                'uri_path' => '/test/path/',
                'templated_path' => '/test/value',
                'evolve' => [
                    ['withHost', ['example.com']],
                    ['withScheme', ['https']],
                ],
            ];

            yield 'multiple optional var first with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value/path/',
                    'template' => [
                        ['withPathParameter', ['var1', 'value']],
                    ],
                ],
            ];

            yield 'multiple optional var second with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/path/value',
                    'template' => [
                        ['withPathParameter', ['var2', 'value']],
                    ],
                ],
            ];

            yield 'multiple optional var both with param using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        ['withPathParameter', ['var2', 'value2']],
                        ['withPathParameter', ['var1', 'value1']],
                    ],
                ],
            ];

            yield 'multiple optional var both with params using pattern: ' . $pattern => [
                [
                    ...$test_case,
                    'templated_path' => '/test/value1/path/value2',
                    'template' => [
                        [
                            'withPathParameters', [
                            [
                                'var1' => 'value1',
                                'var2' => 'value2',
                            ],
                            ],
                        ],
                    ],
                ],
            ];
        }
    }

    public static function provideAddedMethods(): Generator
    {
        yield 'adding post to get' => [
            [HttpMethod::Get],
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Post],
        ];

        yield 'adding delete to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post, HttpMethod::Delete],
            [HttpMethod::Delete],
        ];

        yield 'adding delete and put to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post, HttpMethod::Delete, HttpMethod::Put],
            [HttpMethod::Delete, HttpMethod::Put],
        ];

        yield 'adding get to post and get' => [
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get, HttpMethod::Post],
            [HttpMethod::Get],
        ];
    }

    public static function provideChangedMethods(): Generator
    {
        yield 'single to single' => [
            [HttpMethod::Get],
            [HttpMethod::Post],
            [HttpMethod::Post],
        ];

        yield 'single to multiple' => [
            [HttpMethod::Get],
            [HttpMethod::Post, HttpMethod::Get],
            [HttpMethod::Post, HttpMethod::Get],
        ];

        yield 'multiple to single' => [
            [HttpMethod::Post, HttpMethod::Get],
            [HttpMethod::Get],
            [HttpMethod::Get],
        ];

        yield 'multiple to multiple' => [
            [HttpMethod::Post , HttpMethod::Get ],
            [HttpMethod::Delete , HttpMethod::Patch ],
            [HttpMethod::Delete , HttpMethod::Patch ],
        ];

        yield 'duplicates' => [
            [HttpMethod::Post , HttpMethod::Get ],
            [HttpMethod::Delete , HttpMethod::Patch ],
            [HttpMethod::Delete , HttpMethod::Patch , HttpMethod::Delete , HttpMethod::Patch ],
        ];
    }

    public static function provideNamedSettersAndValues(): Generator
    {
        yield 'withName' => [
            'withName',
            Route::class,
            ['named_route'],
            'named_route',
        ];

        yield 'withHandler' => [
            'withHandler',
            RequestHandlerInterface::class,
            [TestRequestHandler::class],
            TestRequestHandler::class,
        ];

        yield 'withMiddleware (string)' => [
            'withMiddleware',
            MiddlewareInterface::class,
            ['any string'],
            ['any string'],
        ];

        yield 'withMiddleware (array)' => [
            'withMiddleware',
            MiddlewareInterface::class,
            ['a', 'set', 'of', 'strings'],
            ['a', 'set', 'of', 'strings'],
        ];
    }

    public static function provideTestDataWithNamedConstructors(): Generator
    {
        $named_constructors = ['get', 'head', 'post', 'put', 'patch', 'delete'];

        foreach (self::provideTestData() as $label => [$data]) {
            foreach ($named_constructors as $method) {
                yield $method . '() ' . $label => [
                    $data,
                    $method,
                    [HttpMethod::instance($method)->value],
                ];
            }

            yield 'all() ' . $label => [
                $data,
                'all',
                Arr::cast(self::getMethods()),
            ];
        }
    }

    public static function provideTestDataWithMethod(): Generator
    {
        foreach (self::provideTestData() as $label => [$data]) {
            foreach (self::getMethods() as $method) {
                yield $method . ' to ' . $label => [
                    $data,
                    [$method],
                ];
            }

            yield 'all to ' . $label => [
                $data,
                Arr::cast(self::getMethods()),
            ];
        }
    }

    public static function provideTestData(): Generator
    {
        $paths = [
            'simple' => '/example',
            'variable' => '/example/{test}',
            'pattern variable' => '/user/{id:\d+}',
            'optional variable' => '/user/{id:\d+}[/{name}]',
            'optional variables' => '/user[/{id:\d+}[/{name}]]',
        ];

        $attribute_set = [
            'empty' => [],
            'simple' => ['test' => 'attribute'],
            'list' => ['test' => ['attribute', 'other']],
            'nexted' => ['test' => ['attribute' => 'other']],
            'mixed' => [
                'test' => ['attribute' => 'other'],
                'simple' => 'value',
                'int' => 1,
            ],
        ];

        foreach ($paths as $path_label => $path) {
            foreach ($attribute_set as $attribute_label => $attributes) {
                yield $path_label . ' path with ' . $attribute_label . ' attributes'
                => [
                    [
                        'path' => $path,
                        'attributes' => $attributes,
                        'expected_attributes' => $attributes,
                    ],
                ];

                yield $path_label . ' path with ' . $attribute_label . ' (iterable) attributes'
                => [
                    [
                        'path' => $path,
                        'attributes' => new ArrayIterator($attributes),
                        'expected_attributes' => $attributes,
                    ],
                ];
            }
        }
    }

    public static function getMethods(): Generator
    {
        foreach (HttpMethod::cases() as $method) {
            yield $method->value;
        }
    }

    #[TestWith([301])]
    #[TestWith([302])]
    #[TestWith([303])]
    #[TestWith([307])]
    #[TestWith([308])]
    #[Test]
    public function redirect_creates_redirect_route_definition(int $status_code): void
    {
        $route_definition = SUT::redirect('/foo/bar[/index]', '/bar/foo', $status_code);

        self::assertSame(Arr::cast(self::getMethods()), $route_definition->getMethods());
        self::assertSame('/foo/bar[/index]', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(RedirectRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertFalse($route_definition->hasAttribute(MiddlewareInterface::class));
        self::assertNull($route_definition->getAttribute(MiddlewareInterface::class));
        self::assertSame('/bar/foo', $route_definition->getAttribute(RedirectRequestHandler::URI));
        self::assertSame($status_code, $route_definition->getAttribute(RedirectRequestHandler::STATUS_CODE));
    }

    #[Test]
    public function redirect_creates_default_permanent_redirect_route_definition(): void
    {
        $route_definition = SUT::redirect('/foo/bar[/index]', '/bar/foo');

        self::assertSame(Arr::cast(self::getMethods()), $route_definition->getMethods());
        self::assertSame('/foo/bar[/index]', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(RedirectRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertFalse($route_definition->hasAttribute(MiddlewareInterface::class));
        self::assertNull($route_definition->getAttribute(MiddlewareInterface::class));
        self::assertSame('/bar/foo', $route_definition->getAttribute(RedirectRequestHandler::URI));
        self::assertSame(HttpStatus::PERMANENT_REDIRECT, $route_definition->getAttribute(RedirectRequestHandler::STATUS_CODE));
    }

    #[Test]
    public function file_creates_route_definition_for_inline_static_asset(): void
    {
        $static_file = new StaticFile('foo/bar.html', ContentType::HTML);
        $route_definition = SUT::file('/foo/bar/baz', $static_file);

        self::assertSame([HttpMethod::Get->value], $route_definition->getMethods());
        self::assertSame('/foo/bar/baz', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(StaticFileRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertSame($static_file, $route_definition->getAttribute(StaticFile::class));
        self::assertNull($route_definition->getAttribute(HttpHeader::CONTENT_DISPOSITION));
    }

    #[Test]
    public function download_creates_route_definition_for_attachment_static_asset(): void
    {
        $static_file = new StaticFile('foo/bar.html', ContentType::HTML);
        $route_definition = SUT::download('/foo/bar/baz', $static_file);

        self::assertSame([HttpMethod::Get->value], $route_definition->getMethods());
        self::assertSame('/foo/bar/baz', $route_definition->getRoutePath());
        self::assertTrue($route_definition->hasAttribute(RequestHandlerInterface::class));
        self::assertSame(StaticFileRequestHandler::class, $route_definition->getAttribute(RequestHandlerInterface::class));
        self::assertSame($static_file, $route_definition->getAttribute(StaticFile::class));
        self::assertSame('attachment', $route_definition->getAttribute(HttpHeader::CONTENT_DISPOSITION));
    }
}
