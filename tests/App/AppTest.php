<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\App;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\TransformerStrategies\JsonResponseTransformerStrategy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{
    #[Test]
    public function application_lifecycle_happy_path(): void
    {
        $app = App::bootstrap(Context::Test);
        self::assertTrue(App::booted());
        self::assertSame($app, App::bootstrap(Context::Test));
        self::assertSame($app, App::instance());

        self::assertSame('Salt-Lite Framework', $app->config->get('app.name'));
        self::assertSame(JsonResponseTransformerStrategy::class, $app->config->get('http.exceptional_responses.default_transformer'));

        self::assertTrue($app->has(JsonResponseTransformerStrategy::class));
        self::assertInstanceOf(JsonResponseTransformerStrategy::class, $app->get(JsonResponseTransformerStrategy::class));

        self::assertSame(42, $app->call(static fn (): int => 42));

        self::assertSame($app, $app->call(static function (App $arg): App {
            self::assertSame(App::instance(), $arg);
            return $arg;
        }));

        $invokable = new class {
            public function __invoke(JsonResponseTransformerStrategy $strategy): int
            {
                TestCase::assertInstanceOf(JsonResponseTransformerStrategy::class, $strategy);
                return 42;
            }

            public function foo(): string
            {
                return "Hello, World!";
            }
        };

        self::assertSame(42, $app->call($invokable));
        self::assertSame('Hello, World!', $app->call($invokable, 'foo'));

        App::teardown();
        self::assertFalse(App::booted());
    }
}
