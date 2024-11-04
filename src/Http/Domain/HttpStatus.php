<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain;

/**
 * Class Constants to Provide Context of Standard HTTP Status Codes
 *
 * @link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
 */
class HttpStatus
{
    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.2.1
     */
    public const int CONTINUE = 100;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.2.2
     */
    public const int SWITCHING_PROTOCOLS = 101;

    /**
     * The request has succeeded. The payload sent in a 200 response depends
     * on the request method, and is always expected, and is cachable by default.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.1
     */
    public const int OK = 200;

    /**
     * The request has been fulfilled and has resulted in one or more new
     * resources being created. The primary resource created by the request
     * should be identified by either a Location header field in the response
     * or, if no Location field is received, by the effective request URI.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.2
     */
    public const int CREATED = 201;

    /**
     * The request has been accepted for processing, but the processing has not
     * been completed. The request might or might not eventually be acted upon,
     * as it might be disallowed when processing actually takes place.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.3
     */
    public const int ACCEPTED = 202;

    /**
     * The request was successful but the enclosed payload has been modified
     * from that of the origin server's 200 response by a transforming proxy
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.4
     */
    public const int NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The server has successfully fulfilled the request and that there is no
     * additional content to send in the response payload body. Metadata in the
     * response header fields refer to the target resource and its selected
     * representation after the requested action was applied.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.5
     */
    public const int NO_CONTENT = 204;

    /**
     * The server has fulfilled the request and desires that the user agent
     * reset the "document view", which caused the request to be sent, to its
     * original state as received from the origin server.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.3.6
     */
    public const int RESET_CONTENT = 205;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7233#section-4.1
     */
    public const int PARTIAL_CONTENT = 206;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.1
     */
    public const int MULTIPLE_CHOICES = 300;

    /**
     * Indicates that the target resource has been assigned a new permanent URI
     * and any future references to this resource ought to use one of the
     * enclosed URIs. Clients with link-editing capabilities ought to
     * automatically re-link references to the effective request URI to one or
     * more of the new references sent by the server, where possible. A 301
     * response is cacheable by default; i.e., unless otherwise indicated by
     * the method definition or explicit cache controls. Aa user agent MAY
     * change the request method from POST to GET for the subsequent request.
     * If this behavior is undesired, the "307 Temporary Redirect" status
     * code can be used instead.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.2
     */
    public const int MOVED_PERMANENTLY = 301;

    /**
     * Previously "Moved Temporarily", Superseded by 303 and 307
     * Indicates that the target resource resides temporarily under a different
     * URI. Since the redirection might be altered on occasion, the client
     * ought to continue to use the effective request URI for future requests. A
     * user agent MAY change the request method from POST to GET for the
     * subsequent request. If this behavior is undesired, the "307 Temporary
     * Redirect" status code can be used instead.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.3
     */
    public const int FOUND = 302;

    /**
     * The response to the request can be found under another URI using the
     * GET method. When received in response to a POST (or PUT/DELETE), the
     * client should presume that the server has received the data and should
     * issue a new GET request to the given URI.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.4
     */
    public const int SEE_OTHER = 303;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7232#section-4.1
     */
    public const int NOT_MODIFIED = 304;

    /**
     * This status code has been deprecated in RFC 7231 due to security concerns
     * regarding in-band configuration of a proxy.
     *
     * @deprecated
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.5
     */
    public const int USE_PROXY = 305;

    /**
     * Indicates that the target resource resides temporarily under a different
     * URI and the user agent MUST NOT change the request method if it performs
     * an automatic redirection to that URI. Since the redirection can change
     * over time, the client ought to continue using the original effective
     * request URI for future requests.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.7
     */
    public const int TEMPORARY_REDIRECT = 307;

    /**
     * Indicates that the target resource has been assigned a new permanent URI
     * and any future references to this resource ought to use one of the
     * enclosed URIs. Clients with link editing capabilities ought to
     * automatically re-link references to the effective request URI. The user
     * agent MUST NOT change the request method on redirection.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7538#section-3
     */
    public const int PERMANENT_REDIRECT = 308;

    /**
     * Indicates that the server cannot or will not process the request due to
     * something that is perceived to be a client error (e.g., malformed request
     * syntax, invalid request message framing, or deceptive request routing).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.1
     */
    public const int BAD_REQUEST = 400;

    /**
     * Similar to 403 Forbidden, but specifically for use when authentication
     * is required and has failed or has not yet been provided.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7235#section-3.1
     */
    public const int UNAUTHORIZED = 401;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.2
     */
    public const int PAYMENT_REQUIRED = 402;

    /**
     * The server understood, the request but refuses to authorize it.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.3
     */
    public const int FORBIDDEN = 403;

    /**
     * The "404 Not Found" status code indicates that the origin server did not
     * find a current representation for the target resource or is not willing
     * to disclose that one exists. A 404 status code does not indicate whether
     * this lack of representation is temporary or permanent; the "410 Gone"
     * status code is preferred over 404 if the origin server knows, presumably
     * through some configurable means, that the condition is likely to be
     * permanent.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.4
     */
    public const int NOT_FOUND = 404;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.5
     */
    public const int METHOD_NOT_ALLOWED = 405;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.6
     */
    public const int NOT_ACCEPTABLE = 406;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7235#section-3.2
     */
    public const int PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.7
     */
    public const int REQUEST_TIMEOUT = 408;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.8
     */
    public const int CONFLICT = 409;

    /**
     * Indicates that access to the target resource is no longer available at
     * the origin server and that this condition is likely to be permanent. If
     * the server does not or cannot know if the condition is permanent, the
     * "404 Not Found" code should be used instead. Abstractly, the 410 status
     * is "intentional" -- something known to have existed is now known not to
     * exist, while the 404 status is more "accidental".
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.9
     */
    public const int GONE = 410;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.10
     */
    public const int LENGTH_REQUIRED = 411;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7232#section-4.2
     */
    public const int PRECONDITION_FAILED = 412;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.11
     */
    public const int PAYLOAD_TOO_LARGE = 413;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.12
     */
    public const int URI_TOO_LONG = 414;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.13
     */
    public const int UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7233#section-4.4
     */
    public const int RANGE_NOT_SATISFIABLE = 416;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.14
     */
    public const int EXPECTATION_FAILED = 417;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7168#section-2.3.3
     */
    public const int I_AM_A_TEAPOT = 418;

    /**
     * WebDAV: The request was well-formed but was unable to be followed due to
     * semantic errors.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc4918#section-11.2
     */
    public const int UNPROCESSABLE_ENTITY = 422;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.5.15
     */
    public const int UPGRADE_REQUIRED = 426;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585#section-3
     */
    public const int PRECONDITION_REQUIRED = 428;

    /**
     * The 429 status code indicates that the user has sent too many requests
     * in a given amount of time ("rate limiting"). The response
     * representations SHOULD include details explaining the condition, and
     * MAY include a Retry-After header indicating how long to wait before
     * making a new request.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6585#section-4
     */
    public const int TOO_MANY_REQUESTS = 429;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585#section-5
     */
    public const int REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    /**
     * This status code indicates that the server is denying access to the
     * resource as a consequence of a legal demand.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7725
     */
    public const int UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.1
     */
    public const int INTERNAL_SERVER_ERROR = 500;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.2
     */
    public const int NOT_IMPLEMENTED = 501;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.3
     */
    public const int BAD_GATEWAY = 502;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.4
     */
    public const int SERVICE_UNAVAILABLE = 503;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.5
     */
    public const int GATEWAY_TIMEOUT = 504;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc7231#section-6.6.6
     */
    public const int HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @link https://datatracker.ietf.org/doc/html/rfc6585#section-6
     */
    public const int NETWORK_AUTHENTICATION_REQUIRED = 511;
}
