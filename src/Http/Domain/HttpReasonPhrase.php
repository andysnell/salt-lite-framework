<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Domain;

/**
 * @see HttpStatus
 */
class HttpReasonPhrase
{
    public const string CONTINUE = 'Continue';
    public const string SWITCHING_PROTOCOLS = 'Switching Protocols';
    public const string OK = 'OK';
    public const string CREATED = 'Created';
    public const string ACCEPTED = 'Accepted';
    public const string NON_AUTHORITATIVE_INFORMATION = 'Non Authoritative Information';
    public const string NO_CONTENT = 'No Content';
    public const string RESET_CONTENT = 'Reset Content';
    public const string PARTIAL_CONTENT = 'Partial Content';
    public const string MULTIPLE_CHOICES = 'Multiple Choices';
    public const string MOVED_PERMANENTLY = 'Moved Permanently';
    public const string FOUND = 'Found';
    public const string SEE_OTHER = 'See Other';
    public const string NOT_MODIFIED = 'Not Modified';
    public const string USE_PROXY = 'Use Proxy';
    public const string TEMPORARY_REDIRECT = 'Temporary Redirect';
    public const string PERMANENT_REDIRECT = 'Permanent Redirect';
    public const string BAD_REQUEST = 'Bad Request';
    public const string UNAUTHORIZED = 'Unauthorized';
    public const string PAYMENT_REQUIRED = 'Payment Required';
    public const string FORBIDDEN = 'Forbidden';
    public const string NOT_FOUND = 'Not Found';
    public const string METHOD_NOT_ALLOWED = 'Method Not Allowed';
    public const string NOT_ACCEPTABLE = 'Not Acceptable';
    public const string PROXY_AUTHENTICATION_REQUIRED = 'Proxy Authentication Required';
    public const string REQUEST_TIMEOUT = 'Request Timeout';
    public const string CONFLICT = 'Conflict';
    public const string GONE = 'Gone';
    public const string LENGTH_REQUIRED = 'Length Required';
    public const string PRECONDITION_FAILED = 'Precondition Failed';
    public const string PAYLOAD_TOO_LARGE = 'Payload Too Large';
    public const string URI_TOO_LONG = 'URI Too Long';
    public const string UNSUPPORTED_MEDIA_TYPE = 'Unsupported Media Type';
    public const string RANGE_NOT_SATISFIABLE = 'Range Not Satisfiable';
    public const string EXPECTATION_FAILED = 'Expectation Failed';
    public const string I_AM_A_TEAPOT = 'I Am A Teapot';
    public const string UNPROCESSABLE_ENTITY = 'Unprocessable Entity';
    public const string UPGRADE_REQUIRED = 'Upgrade Required';
    public const string PRECONDITION_REQUIRED = 'Precondition Required';
    public const string TOO_MANY_REQUESTS = 'Too Many Requests';
    public const string REQUEST_HEADER_FIELDS_TOO_LARGE = 'Request Header Fields Too Large';
    public const string UNAVAILABLE_FOR_LEGAL_REASONS = 'Unavailable For Legal Reasons';
    public const string INTERNAL_SERVER_ERROR = 'Internal Server Error';
    public const string NOT_IMPLEMENTED = 'Not Implemented';
    public const string BAD_GATEWAY = 'Bad Gateway';
    public const string SERVICE_UNAVAILABLE = 'Service Unavailable';
    public const string GATEWAY_TIMEOUT = 'Gateway Timeout';
    public const string HTTP_VERSION_NOT_SUPPORTED = 'HTTP Version Not Supported';
    public const string NETWORK_AUTHENTICATION_REQUIRED = 'Network Authentication Required';

    private const array MAP = [
        HttpStatus::CONTINUE => self::CONTINUE,
        HttpStatus::SWITCHING_PROTOCOLS => self::SWITCHING_PROTOCOLS,
        HttpStatus::OK => self::OK,
        HttpStatus::CREATED => self::CREATED,
        HttpStatus::ACCEPTED => self::ACCEPTED,
        HttpStatus::NON_AUTHORITATIVE_INFORMATION => self::NON_AUTHORITATIVE_INFORMATION,
        HttpStatus::NO_CONTENT => self::NO_CONTENT,
        HttpStatus::RESET_CONTENT => self::RESET_CONTENT,
        HttpStatus::PARTIAL_CONTENT => self::PARTIAL_CONTENT,
        HttpStatus::MULTIPLE_CHOICES => self::MULTIPLE_CHOICES,
        HttpStatus::MOVED_PERMANENTLY => self::MOVED_PERMANENTLY,
        HttpStatus::FOUND => self::FOUND,
        HttpStatus::SEE_OTHER => self::SEE_OTHER,
        HttpStatus::NOT_MODIFIED => self::NOT_MODIFIED,
        HttpStatus::USE_PROXY => self::USE_PROXY, /** @phpstan-ignore-line */
        HttpStatus::TEMPORARY_REDIRECT => self::TEMPORARY_REDIRECT,
        HttpStatus::PERMANENT_REDIRECT => self::PERMANENT_REDIRECT,
        HttpStatus::BAD_REQUEST => self::BAD_REQUEST,
        HttpStatus::UNAUTHORIZED => self::UNAUTHORIZED,
        HttpStatus::PAYMENT_REQUIRED => self::PAYMENT_REQUIRED,
        HttpStatus::FORBIDDEN => self::FORBIDDEN,
        HttpStatus::NOT_FOUND => self::NOT_FOUND,
        HttpStatus::METHOD_NOT_ALLOWED => self::METHOD_NOT_ALLOWED,
        HttpStatus::NOT_ACCEPTABLE => self::NOT_ACCEPTABLE,
        HttpStatus::PROXY_AUTHENTICATION_REQUIRED => self::PROXY_AUTHENTICATION_REQUIRED,
        HttpStatus::REQUEST_TIMEOUT => self::REQUEST_TIMEOUT,
        HttpStatus::CONFLICT => self::CONFLICT,
        HttpStatus::GONE => self::GONE,
        HttpStatus::LENGTH_REQUIRED => self::LENGTH_REQUIRED,
        HttpStatus::PRECONDITION_FAILED => self::PRECONDITION_FAILED,
        HttpStatus::PAYLOAD_TOO_LARGE => self::PAYLOAD_TOO_LARGE,
        HttpStatus::URI_TOO_LONG => self::URI_TOO_LONG,
        HttpStatus::UNSUPPORTED_MEDIA_TYPE => self::UNSUPPORTED_MEDIA_TYPE,
        HttpStatus::RANGE_NOT_SATISFIABLE => self::RANGE_NOT_SATISFIABLE,
        HttpStatus::EXPECTATION_FAILED => self::EXPECTATION_FAILED,
        HttpStatus::I_AM_A_TEAPOT => self::I_AM_A_TEAPOT,
        HttpStatus::UNPROCESSABLE_ENTITY => self::UNPROCESSABLE_ENTITY,
        HttpStatus::UPGRADE_REQUIRED => self::UPGRADE_REQUIRED,
        HttpStatus::PRECONDITION_REQUIRED => self::PRECONDITION_REQUIRED,
        HttpStatus::TOO_MANY_REQUESTS => self::TOO_MANY_REQUESTS,
        HttpStatus::REQUEST_HEADER_FIELDS_TOO_LARGE => self::REQUEST_HEADER_FIELDS_TOO_LARGE,
        HttpStatus::UNAVAILABLE_FOR_LEGAL_REASONS => self::UNAVAILABLE_FOR_LEGAL_REASONS,
        HttpStatus::INTERNAL_SERVER_ERROR => self::INTERNAL_SERVER_ERROR,
        HttpStatus::NOT_IMPLEMENTED => self::NOT_IMPLEMENTED,
        HttpStatus::BAD_GATEWAY => self::BAD_GATEWAY,
        HttpStatus::SERVICE_UNAVAILABLE => self::SERVICE_UNAVAILABLE,
        HttpStatus::GATEWAY_TIMEOUT => self::GATEWAY_TIMEOUT,
        HttpStatus::HTTP_VERSION_NOT_SUPPORTED => self::HTTP_VERSION_NOT_SUPPORTED,
        HttpStatus::NETWORK_AUTHENTICATION_REQUIRED => self::NETWORK_AUTHENTICATION_REQUIRED,
    ];

    /**
     * If the $status_code parameter does not match one of the predefined status
     * code values, an empty string is returned as the reason phrase. This is
     * intentional to comply with the PSR-7 ResponseInterface::getReasonPhrase()
     * definition and the Laminas Diactoros implementation.
     */
    public static function lookup(int $status_code): string
    {
        return self::MAP[$status_code] ?? '';
    }
}
