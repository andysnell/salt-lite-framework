<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;

class DeadRouteResponse extends GenericHttpExceptionResponse
{
    protected int $status_code = HttpStatus::GONE;
    protected string $title = HttpReasonPhrase::GONE;
    protected string $detail = 'This functionality is no longer supported.';
}
