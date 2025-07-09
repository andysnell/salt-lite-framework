<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\App\App;
use PhoneBurner\SaltLite\Cache\Lock\LockFactory;
use PhoneBurner\SaltLite\Container\ServiceFactory;
use PhoneBurner\SaltLite\Cryptography\Natrium;
use PhoneBurner\SaltLite\Framework\Database\Redis\RedisManager;
use PhoneBurner\SaltLite\Framework\Http\Config\SessionConfigStruct;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\CompressingSessionHandlerDecorator;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\CookieSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\EncodingSessionHandlerDecorator;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\EncryptingSessionHandlerDecorator;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\FileSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\InMemorySessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\LockingSessionHandlerDecorator;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\NullSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\RedisSessionHandler;
use PhoneBurner\SaltLite\Http\Cookie\CookieJar;
use PhoneBurner\SaltLite\Random\Randomizer;
use PhoneBurner\SaltLite\String\Encoding\Encoding;
use PhoneBurner\SaltLite\Time\Clock\Clock;
use PhoneBurner\SaltLite\Type\Type;

use function PhoneBurner\SaltLite\Framework\ghost;

class SessionHandlerServiceFactory implements ServiceFactory
{
    public function __invoke(App $app, string $id): SessionHandler
    {
        $config = Type::of(SessionConfigStruct::class, $app->config->get('http.session'));

        $handler = match ($config->handler->getSessionHandlerClass()->value) {
            RedisSessionHandler::class => ghost(static fn(RedisSessionHandler $ghost): null => $ghost->__construct(
                $app->get(RedisManager::class)->connect(),
                $config->ttl,
            )),
            FileSessionHandler::class => new FileSessionHandler(
                $app->get(Clock::class),
                $config->ttl,
                $app->get(Randomizer::class),
                $config->file_path,
            ),
            CookieSessionHandler::class => new CookieSessionHandler(
                $app->get(CookieJar::class),
                $config->ttl,
            ),
            InMemorySessionHandler::class => new InMemorySessionHandler(),
            NullSessionHandler::class => new NullSessionHandler(),
            default => throw new \LogicException('Undefined session handler type'),
        };

        $wrapped_handler = $handler;

        // Always encode the session data if the handler is CookieSessionHandler
        // This isn't necessary for other handlers that are binary safe like Redis
        if ($config->encoding || $handler instanceof CookieSessionHandler) {
            $wrapped_handler = new EncodingSessionHandlerDecorator(
                $wrapped_handler,
                $config->encoding ?? Encoding::Base64UrlNoPadding,
            );
        }

        // Always encrypt the session data if the handler is CookieSessionHandler
        if ($config->encrypt || $handler instanceof CookieSessionHandler) {
            $wrapped_handler = new EncryptingSessionHandlerDecorator(
                $wrapped_handler,
                $app->get(Natrium::class),
            );
        }

        // Always compress the session data if the handler is CookieSessionHandler
        if ($config->compress || $handler instanceof CookieSessionHandler) {
            $wrapped_handler = new CompressingSessionHandlerDecorator(
                $wrapped_handler,
            );
        }

        if ($config->lock_sessions) {
            return new LockingSessionHandlerDecorator(
                $wrapped_handler,
                $app->get(LockFactory::class),
            );
        }

        return $wrapped_handler;
    }
}
