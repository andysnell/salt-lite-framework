<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Response\Exceptional;

use PhoneBurner\Http\Message\ResponseWrapper;
use Psr\Http\Message\ResponseInterface;

class ResponseException extends \RuntimeException implements ResponseInterface
{
    use ResponseWrapper;

    public function __construct(
        ResponseInterface $response,
        string $message = '',
        \Throwable|null $previous = null,
    ) {
        parent::__construct($message, $response->getStatusCode(), $previous);
        $this->setWrapped($response);
    }

    #[\Override]
    final protected function wrap(ResponseInterface $response): static
    {
        $this->setWrapped($response);
        return $this;
    }
}
