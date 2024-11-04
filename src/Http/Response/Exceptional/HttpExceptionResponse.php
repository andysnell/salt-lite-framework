<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use Psr\Http\Message\ResponseInterface;
use Throwable;

interface HttpExceptionResponse extends Throwable, ResponseInterface
{
    public function getStatusCode(): int;

    public function getStatusTitle(): string;

    public function getStatusDetail(): string;

    public function getHeaders(): array;

    public function getAdditional(): array;
}
