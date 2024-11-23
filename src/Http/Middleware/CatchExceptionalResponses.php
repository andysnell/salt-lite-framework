<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Middleware;

use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Domain\HttpStatus;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\StreamResponse;
use PhoneBurner\SaltLite\Framework\Util\Helper\Psr7;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class CatchExceptionalResponses implements MiddlewareInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly BuildStage $stage,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Throwable $e) {
            if ($e instanceof ResponseInterface) {
                return $e;
            }

            $this->logger->error('Caught Exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return $this->stage === BuildStage::Development
                ? $this->whoops($e, $request)
                : new ServerErrorResponse();
        }
    }

    private function whoops(\Throwable $e, ServerRequestInterface $request): ResponseInterface
    {
        $handler = match (true) {
            Psr7::expectsJson($request) => new JsonResponseHandler(),
            Psr7::expectsHtml($request) => new PrettyPageHandler(),
            default => new PlainTextHandler(),
        };

        $whoops = new Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);
        $whoops->pushHandler($handler);

        return new StreamResponse(
            $whoops->handleException($e),
            HttpStatus::INTERNAL_SERVER_ERROR,
            [HttpHeader::CONTENT_TYPE => $handler->contentType()],
        );
    }
}
