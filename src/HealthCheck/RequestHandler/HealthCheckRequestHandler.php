<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\HealthCheck\RequestHandler;

use PhoneBurner\SaltLite\Framework\HealthCheck\Domain\HealthStatus;
use PhoneBurner\SaltLite\Framework\HealthCheck\HealthCheckBuilder;
use PhoneBurner\SaltLite\Http\Domain\ContentType;
use PhoneBurner\SaltLite\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Http\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class HealthCheckRequestHandler implements RequestHandlerInterface
{
    public const string DEFAULT_ENDPOINT = '/healthz';

    public const array HEALTH_CHECK_HEADERS = [
        HttpHeader::CONTENT_TYPE => ContentType::HEALTH_JSON,
        HttpHeader::CACHE_CONTROL => 'no-store',
    ];

    public function __construct(
        private readonly HealthCheckBuilder $factory,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $health_check = $this->factory->withLinks([
            'self' => $request->getUri()->getPath(),
        ])->make();

        $this->logger->debug('application health check: ' . $health_check->status->value);

        return new JsonResponse(
            $health_check,
            $health_check->status === HealthStatus::Pass ? HttpStatus::OK : HttpStatus::SERVICE_UNAVAILABLE,
            self::HEALTH_CHECK_HEADERS,
            JsonResponse::DEFAULT_JSON_FLAGS | \JSON_PRETTY_PRINT,
        );
    }
}
