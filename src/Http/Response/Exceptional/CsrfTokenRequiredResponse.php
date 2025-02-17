<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

/**
 * A "403 Forbidden" is the most appropriate status code for a CSRF token failure:
 *  - It is a 4xx client side type error.
 *  - The request is understood and assumed to be otherwise valid.
 *  - The user is authenticated, as CSRF relies on user session context.
 *  - The server refuses further action because the token is invalid.
 *
 * Thus, the user does not have permission to act on the resource because the token is invalid.
 * and we can use the "403 Forbidden" status code to indicate this.
 */
class CsrfTokenRequiredResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::FORBIDDEN;
    protected string|null $http_reason_phrase = HttpReasonPhrase::FORBIDDEN;
    protected string $title = 'CSRF Token Required';
    protected string $detail = 'You do not have permission to act on the requested resource, as the required CSRF token is missing or invalid.';
}
