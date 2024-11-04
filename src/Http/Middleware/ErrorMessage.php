<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

final class ErrorMessage
{
    public const string INVALID_CLASS = '"%s" Does Not Implement MiddlewareInterface';
    public const string RESOLUTION_ERROR = 'Cannot Resolve "%s" to MiddlewareInterface or Middleware Group';
    public const string FALLBACK_HANDLER_NOT_SET = 'Terminable Middleware Fallback Handler Not Set';
}
