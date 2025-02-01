<?php

declare(strict_types=1);

namespace Http\Routing\Match;

use Generator;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpMethod;
use PhoneBurner\SaltLite\Framework\Http\Routing\Definition\RouteDefinition;
use PhoneBurner\SaltLite\Framework\Http\Routing\Match\RouteMatch as SUT;
use PhoneBurner\SaltLite\Framework\Http\Routing\RequestHandler\NotFoundRequestHandler;
use PhoneBurner\SaltLite\Framework\Tests\Fixtures\TestRequestHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteMatchTest extends TestCase
{
    #[DataProvider('provideConstructorArgs')]
    #[Test]
    public function make_takes_RouteDefinition_and_path_vars(
        RouteDefinition $definition,
        array $path_parameters,
        array $expected_attributes,
    ): void {
        $sut = SUT::make($definition, $path_parameters);

        self::assertSame($expected_attributes, $sut->getAttributes());
        self::assertSame($path_parameters, $sut->getPathParameters());
    }

    #[DataProvider('providePathParameters')]
    #[Test]
    public function getPathParameter_returns_expected_value(
        array $vars,
        string $name,
        string|null $expected_value,
        string|null $default = null,
    ): void {
        $route = SUT::make(RouteDefinition::get('/test'), $vars);

        if ($default === null) {
            self::assertEquals($expected_value, $route->getPathParameter($name));
        }

        self::assertEquals($expected_value, $route->getPathParameter($name, $default));
    }

    #[Test]
    public function getHandler_returns_valid_handler_class(): void
    {
        $route = SUT::make(RouteDefinition::get('/test', [
            RequestHandlerInterface::class => TestRequestHandler::class,
        ]), []);

        self::assertSame(TestRequestHandler::class, $route->getHandler());
    }

    #[DataProvider('provideConstructorArgs')]
    #[Test]
    public function withPathParameter_maintains_state(
        RouteDefinition $definition,
        array $path_parameters,
        array $expected_attributes,
    ): void {
        $definition = $definition->withRoutePath('/test/{var}');
        $path_parameters['var'] = 'existing';

        $sut = SUT::make($definition, $path_parameters);
        $new = $sut->withPathParameter('var', 'new value');

        self::assertSame($expected_attributes, $sut->getAttributes());
        self::assertSame($path_parameters, $sut->getPathParameters());

        self::assertSame($expected_attributes, $new->getAttributes());
        self::assertSame($path_parameters, $new->getPathParameters());
    }

    #[DataProvider('provideConstructorArgs')]
    #[Test]
    public function withPathParameters_maintains_state(
        RouteDefinition $definition,
        array $path_parameters,
        array $expected_attributes,
    ): void {
        $definition = $definition->withRoutePath('/test/{var}');
        $path_parameters['var'] = 'existing';

        $sut = SUT::make($definition, $path_parameters);
        $new = $sut->withPathParameters([
            'var' => 'new value',
            'another' => 'value',
        ]);

        self::assertSame($expected_attributes, $sut->getAttributes());
        self::assertSame($path_parameters, $sut->getPathParameters());

        self::assertSame($expected_attributes, $new->getAttributes());
        self::assertSame($path_parameters, $new->getPathParameters());
    }

    #[Test]
    public function wrapped_Uri_has_path_vars_set(): void
    {
        $definition = RouteDefinition::get('/test/{var1}/{var2}');

        $sut = SUT::make($definition, [
            'var1' => 'path1',
            'var2' => 'path2',
        ]);

        self::assertSame('/test/path1/path2', $sut->getPath());

        $sut = SUT::make($definition, [
            'var1' => 'path1',
        ]);

        self::assertSame('/test/path1/', $sut->getPath());
        self::assertSame(
            '/test/path1/set2',
            $sut->withPathParameter('var2', 'set2')->getPath(),
        );

        self::assertSame(
            '/test/set1/set2',
            $sut->withPathParameter('var1', 'set1')->withPathParameter('var2', 'set2')->getPath(),
        );

        self::assertSame(
            '/test//',
            $sut->withPathParameters([
                'not' => 'in_path',
            ])->getPath(),
        );

        self::assertSame(
            '/test//set2',
            $sut->withPathParameters([])->withPathParameter('var2', 'set2')->getPath(),
        );
    }

    #[DataProvider('provideUriTestCase')]
    #[Test]
    public function wrapped_Uri_has_expected_path(array $test_case): void
    {
        $def = RouteDefinition::get($test_case['path']);

        // a match without any path vars
        $sut = SUT::make($def, []);

        self::assertSame($test_case['uri_path'], (string)$sut);

        if ($test_case['templated_path']) {
            $uri = $sut;
            foreach ($test_case['template'] as [$method, $args]) {
                $uri = $uri->$method(...$args);
            }

            self::assertSame($test_case['templated_path'], (string)$uri);
        }
    }

    public static function provideUriTestCase(): Generator
    {
        yield 'no vars' => [[
            'path' => '/test',
            'uri_path' => '/test',
            'evolved_path' => 'https://example.com/test',
            'templated_path' => '/test',
            'template' => [
                ['withPathParameter', ['any', 'data']],
            ],
        ],];

        $patterns = ['', ':\d+', ':(?:en|de)'];

        foreach ($patterns as $pattern) {
            $test_case = [
                'path' => '/test/{var' . $pattern . '}',
                'uri_path' => '/test/',
                'evolved_path' => 'https://example.com/test/',
                'templated_path' => '/test/value',
            ];

            yield 'single var with param: var' . $pattern => [
                [
                    ...$test_case,
                    'template' => [
                        ['withPathParameter', ['var', 'value']],
                    ],
                ],
            ];

            yield 'single var with params: var' . $pattern => [
                [
                    ...$test_case,
                    'template' => [
                        ['withPathParameters', [['var' => 'value']]],
                    ],
                ],
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
                        ['withPathParameters', [['var1' => 'value1', 'var2' => 'value2']]],
                    ],
                ],
            ];

            $test_case = [
                'path' => "/test/[{var1{$pattern}}/]path/{var2{$pattern}}",
                'uri_path' => '/test/path/',
                'templated_path' => '/test/value',
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
                        ['withPathParameters', [['var1' => 'value1', 'var2' => 'value2',]]],
                    ],
                ],
            ];
        }
    }

    public static function provideConstructorArgs(): Generator
    {
        $attributes = [
            'string' => 'data',
            'array' => ['data'],
            'int' => 1,
            'bool' => true,
        ];

        $attributes_with_defaulted_values = [
            RequestHandlerInterface::class => NotFoundRequestHandler::class,
            MiddlewareInterface::class => [],
            ...$attributes,
        ];

        $attributes_with_set_defaulted_values = [
            RequestHandlerInterface::class => TestRequestHandler::class,
            MiddlewareInterface::class => ['test'],
            ...$attributes,
        ];

        foreach ([[], ['var1' => 'value1', 'var2' => 'value2']] as $path_params) {
            // methods shouldn't matter, but try all of them
            foreach (HttpMethod::cases() as $method) {
                yield [
                    RouteDefinition::make('/path', [$method], $attributes),
                    $path_params,
                    $attributes_with_defaulted_values,
                ];

                yield [
                    RouteDefinition::make('/path', [$method], $attributes_with_defaulted_values),
                    $path_params,
                    $attributes_with_defaulted_values,
                ];

                yield [
                    RouteDefinition::make('/path', [$method], $attributes_with_set_defaulted_values),
                    $path_params,
                    $attributes_with_set_defaulted_values,
                ];
            }
        }
    }

    public static function providePathParameters(): Generator
    {
        yield [
            [
                'foo' => 'bar',
            ],
            'foo',
            'bar',
        ];

        yield [
            [],
            'foo',
            'default',
            'default',
        ];

        yield [
            [],
            'foo',
            null,
        ];
    }
}
