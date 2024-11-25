<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

/**
 * Use when a request requires authentication, but none was provided, the provided
 * authentication credentials are invalid. If the user is able to authenticate
 * successfully in a subsequent request, they may have permission to access the
 * resource.
 *
 * @see AuthorizationRequiredResponse for when a user is authenticated but does not have permission.
 */
class AuthenticationRequiredResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::UNAUTHORIZED;
    protected string|null $http_reason_phrase = HttpReasonPhrase::UNAUTHORIZED;
    protected string $title = HttpReasonPhrase::UNAUTHORIZED;
    protected string $detail = 'Authentication is required and has failed or has not yet been provided.';
}
