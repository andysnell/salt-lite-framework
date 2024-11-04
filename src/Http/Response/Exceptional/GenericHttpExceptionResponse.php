<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\Http\Message\ResponseWrapper;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpReasonPhrase;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\TextResponse;

class GenericHttpExceptionResponse extends ResponseException implements HttpExceptionResponse
{
    use ResponseWrapper;

    protected int $status_code = HttpStatus::INTERNAL_SERVER_ERROR;
    protected string $title = HttpReasonPhrase::INTERNAL_SERVER_ERROR;
    protected string|null $http_reason_phrase = null;
    protected string $detail = '';
    protected array $additional = [];

    public function __construct(
        int|null $status_code = null,
        string|null $title = null,
        string|null $detail = null,
        array $additional = [],
        array $headers = [],
        \Throwable|null $previous = null,
    ) {
        $this->status_code = $status_code ?? $this->status_code;
        $this->title = $title ?? $this->title;
        $this->detail = $detail ?? $this->detail;
        $this->additional = $additional ?: $this->additional;
        $message = \sprintf('HTTP %s: %s', $this->status_code, $this->http_reason_phrase ?? $this->title);
        $response = new TextResponse($message, $this->status_code, $headers);
        parent::__construct($response, $message, $previous);
    }

    #[\Override]
    public function getStatusTitle(): string
    {
        return $this->title;
    }

    #[\Override]
    public function getStatusDetail(): string
    {
        return $this->additional['detail'] ?? $this->detail;
    }

    #[\Override]
    public function getAdditional(): array
    {
        return $this->additional;
    }
}
