<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Routing\RequestHandler;

use PhoneBurner\SaltLite\Framework\Http\Domain\HttpHeader;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\FileNotFoundResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\Exceptional\ServerErrorResponse;
use PhoneBurner\SaltLite\Framework\Http\Response\StreamResponse;
use PhoneBurner\SaltLite\Framework\Routing\Domain\StaticFile;
use PhoneBurner\SaltLite\Framework\Routing\Match\RouteMatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StaticFileRequestHandler implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route_attributes = $request->getAttribute(RouteMatch::class)?->getAttributes() ?? [];

        $file = $route_attributes[StaticFile::class] ?? null;
        if (! $file instanceof StaticFile) {
            return new ServerErrorResponse();
        }

        $stream = @\fopen($file->path, 'rb');
        if ($stream === false) {
            return new FileNotFoundResponse();
        }

        return new StreamResponse($stream, headers: [
            HttpHeader::CONTENT_TYPE => $file->content_type,
            HttpHeader::CONTENT_LENGTH => \filesize($file->path),
            HttpHeader::CONTENT_DISPOSITION => $route_attributes[HttpHeader::CONTENT_DISPOSITION] ?? 'inline',
        ]);
    }
}
