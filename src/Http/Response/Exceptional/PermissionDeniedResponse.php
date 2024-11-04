<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

class PermissionDeniedResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::FORBIDDEN;
    protected string $title = 'Permission Denied';
    protected string|null $http_reason_phrase = HttpReasonPhrase::FORBIDDEN;
    protected string $detail = 'You do not have permission to access the requested resource.';
}
