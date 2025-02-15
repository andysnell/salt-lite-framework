<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http;

use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Kernel;
use PhoneBurner\SaltLite\Framework\Http\Event\EmittingHttpResponseComplete;
use PhoneBurner\SaltLite\Framework\Http\Event\EmittingHttpResponseFailed;
use PhoneBurner\SaltLite\Framework\Http\Event\EmittingHttpResponseStart;
use PhoneBurner\SaltLite\Framework\Http\Event\HandlingHttpRequestComplete;
use PhoneBurner\SaltLite\Framework\Http\Event\HandlingHttpRequestFailed;
use PhoneBurner\SaltLite\Framework\Http\Event\HandlingHttpRequestStart;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpKernel implements Kernel
{
    public function __construct(
        private readonly RequestFactory $request_factory,
        private readonly RequestHandlerInterface $request_handler,
        private readonly EmitterInterface $emitter,
        private readonly BuildStage $stage,
        private readonly EventDispatcherInterface $event_dispatcher,
    ) {
    }

    #[\Override]
    public function run(ServerRequestInterface|null $request = null): void
    {
        try {
            $request ??= $this->request_factory->fromGlobals();
            $this->event_dispatcher->dispatch(new HandlingHttpRequestStart($request));
            $response = $this->request_handler->handle($request);
            $this->event_dispatcher->dispatch(new HandlingHttpRequestComplete($request, $response));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new HandlingHttpRequestFailed($request, $e));
            $response = $this->stage !== BuildStage::Development ? new ServerErrorResponse() : throw $e;
        }

        try {
            $this->event_dispatcher->dispatch(new EmittingHttpResponseStart($response));
            $this->emitter->emit($response);
            $this->event_dispatcher->dispatch(new EmittingHttpResponseComplete($response));
        } catch (\Throwable $e) {
            $this->event_dispatcher->dispatch(new EmittingHttpResponseFailed($response, $e));
            if ($this->stage === BuildStage::Development) {
                throw $e;
            }

            // This is a very bad place to end up; some kind of failure happened
            // while trying to emit the response. We can't send a response back,
            // or even reliably echo out an error message. If we don't suppress
            // the exception here, it's possible we'll leak sensitive information.
            // Getting the "white screen of death" is the best case scenario here.
        }
    }
}
