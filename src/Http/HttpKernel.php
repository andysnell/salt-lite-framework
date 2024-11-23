<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Kernel;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class HttpKernel implements Kernel
{
    public function __construct(
        private readonly RequestFactory $request_factory,
        private readonly RequestHandlerInterface $request_handler,
        private readonly EmitterInterface $emitter,
        private readonly LoggerInterface $logger,
        private readonly BuildStage $stage,
    ) {
    }

    #[\Override]
    public function run(ServerRequestInterface|null $request = null): void
    {
        try {
            $request ??= $this->request_factory->fromGlobals();
            $this->logger->debug('Processing request: ' . $request->getMethod() . ' ' . $request->getUri());
            $response = $this->request_handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->error('An unhandled error occurred while processing the request', [
                'exception' => $e,
            ]);

            if ($this->stage === BuildStage::Development) {
                throw $e;
            }

            $response = new ServerErrorResponse();
        }

        try {
            $this->emitter->emit($response);
        } catch (\Throwable $e) {
            $this->logger->critical('An unhandled error occurred while emitting the request', [
                'exception' => $e,
            ]);

            if ($this->stage === BuildStage::Development) {
                throw $e;
            }
        }
    }
}
