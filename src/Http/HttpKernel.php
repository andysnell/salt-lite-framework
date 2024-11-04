<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use PhoneBurner\SaltLite\Framework\App\Kernel;
use PhoneBurner\SaltLite\Framework\Http\Response\HtmlResponse;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class HttpKernel implements Kernel
{
    public function __construct(
        private readonly RequestFactory $request_factory,
        private readonly RequestHandlerInterface $request_handler,
        private readonly EmitterInterface $emitter,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[\Override]
    public function run(): void
    {
        try {
            $request = $this->request_factory->fromGlobals();
            $this->logger->debug('Processing request: ' . $request->getMethod() . ' ' . $request->getUri());
            $response = $this->request_handler->handle($request);
        } catch (\Throwable $e) {
            $this->logger->error('An unhandled error occurred while processing the request', [
                'exception' => $e,
            ]);

            $whoops = new Run();
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
            $whoops->pushHandler(new PrettyPageHandler());
            $html = $whoops->handleException($e);

            $response = new HtmlResponse($html);
        }

        try {
            $this->emitter->emit($response);
        } catch (\Throwable $e) {
            $this->logger->critical('An unhandled error occurred while emitting the request', [
                'exception' => $e,
            ]);
        }
    }
}
