<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

/**
 * Use when an authenticated user does not have permission to access a resource.
 * This was formerly called "Permission Denied" in Salt, but that is a bit harsh,
 * and less user-friendly than "Authorization Required".
 *
 * @see AuthenticationRequiredResponse for when a user is not authenticated
 */
class AuthorizationRequiredResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::FORBIDDEN;
    protected string|null $http_reason_phrase = HttpReasonPhrase::FORBIDDEN;
    protected string $title = 'Authorization Required';
    protected string $detail = 'You do not have permission to access the requested resource.';
}
