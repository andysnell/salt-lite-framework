<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

class ServerErrorResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::INTERNAL_SERVER_ERROR;
    protected string $title = HttpReasonPhrase::INTERNAL_SERVER_ERROR;
    protected string $detail = 'An internal server error occurred.';
}
