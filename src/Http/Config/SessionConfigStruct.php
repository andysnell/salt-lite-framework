<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Http\Session\SessionHandlerType;
use PhoneBurner\SaltLite\Serialization\Serializer;
use PhoneBurner\SaltLite\String\Encoding\Encoding;
use PhoneBurner\SaltLite\Time\TimeConstant;
use PhoneBurner\SaltLite\Time\Ttl;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

final readonly class SessionConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param SessionHandlerType $handler
     * The session handler to use for storing session data. Note that we do not
     * support PHP native session handing. The available options are:
     * - Redis: stores session data in a Redis server.
     * - File: stores session data in files on the server.
     * - InMemory: stores session data in memory, used for testing only.
     *
     * @param Ttl $ttl
     * The time-to-live for the session data, updated every time the session is accessed.
     *
     * @param bool $lock_sessions
     * If enabled, the session manager will lock access to the session data while
     * the request is processing, releasing the lock after the session write operation.
     * This is useful in preventing clobbering data when multiple AJAX requests
     * are made in parallel. The downside is that it can cause a bottleneck in
     * high-traffic applications, as only one request can access session-aware
     * routes at a time. Requests will block for up to 30 seconds waiting for the lock.
     *
     * @param bool $encrypt
     * If enabled, the session manager will encrypt the all session data before
     * writing it to the session store. Certain handlers like the CookieSessionHandler
     * will force this to be enabled.
     *
     * @param bool $add_xsrf_token_cookie
     * Some JavaScript libraries send the value
     * of the `XSRF-TOKEN` cookie in the X-XSRF-TOKEN header when making same-origin
     * requests. If enabled, the session manager will set the `XSRF-TOKEN` cookie
     * to the encrypted value of the CSRF token, and make it accessible to JavaScript.
     *
     * @param string $file_path
     * Specific only to the FileSessionHandler, the path to the directory where
     * session files will be stored. This directory must be writable by the web server.
     * If the directory does not exist, it will be created.
     */
    public function __construct(
        public SessionHandlerType $handler = SessionHandlerType::Redis,
        public Ttl $ttl = new Ttl(TimeConstant::SECONDS_IN_HOUR),
        public bool $lock_sessions = false,
        public bool $encrypt = false,
        public bool $compress = false,
        public Encoding|null $encoding = null,
        public bool $add_xsrf_token_cookie = false,
        public Serializer $serializer = Serializer::Igbinary,
        public string $file_path = APP_ROOT . '/storage/sessions',
    ) {
    }
}
