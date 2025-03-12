<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\Attribute\Usage\Contract;
use PhoneBurner\SaltLite\Enum\EnumCaseAttr;
use PhoneBurner\SaltLite\Framework\Http\Session\Attribute\MapsToSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\CookieSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\FileSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\InMemorySessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\NullSessionHandler;
use PhoneBurner\SaltLite\Framework\Http\Session\Handler\RedisSessionHandler;
use PhoneBurner\SaltLite\String\ClassString\ClassString;

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

    /**
     * @return ClassString<\SessionHandlerInterface>
     */
    public function getSessionHandlerClass(): ClassString
    {
        return EnumCaseAttr::fetch($this, MapsToSessionHandler::class)->mapsTo();
    }
}
