<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\Framework\Http\Session\Attribute\MapsToSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\CookieSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\FileSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\InMemorySessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\NullSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\RedisSessionHandler;
use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;
use PhoneBurner\SaltLite\Framework\Util\ClassString;
use PhoneBurner\SaltLite\Framework\Util\Helper\CaseAttr;

#[Contract]
enum SessionHandlerType
{
    #[MapsToSessionHandler(RedisSessionHandler::class)]
    case Redis;

    #[MapsToSessionHandler(FileSessionHandler::class)]
    case File;

    #[MapsToSessionHandler(CookieSessionHandler::class)]
    case Cookie;

    #[MapsToSessionHandler(InMemorySessionHandler::class)]
    case InMemory;

    #[MapsToSessionHandler(NullSessionHandler::class)]
    case Null;

    /** @return ClassString<\SessionHandlerInterface> */
    public function getSessionHandlerClass(): ClassString
    {
        return CaseAttr::first($this, MapsToSessionHandler::class)?->mapsTo() ?? throw new \LogicException(
            'no session handler defined for this case',
        );
    }
}
