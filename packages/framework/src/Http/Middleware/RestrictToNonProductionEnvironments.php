<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Middleware;

use PhoneBurner\Pinch\Component\Configuration\BuildStage;
use PhoneBurner\Pinch\Component\Http\Middleware\TerminableMiddleware;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\NotFoundResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Prevents the route from being accessed in production environments but allows
 * it to be accessed in both development and staging environments. Note that the
 * build stage is not a route or request attribute but is instead determined by
 * the environment and injected by the service container.
 *
 * By default, if this middleware is accessed in a production environment, the
 * request will be passed to the fallback request handler to handle, skipping any
 * further middleware. This is most likely going to be a 404 response.
 */
class RestrictToNonProductionEnvironments implements TerminableMiddleware
{
    private RequestHandlerInterface|null $fallback_request_handler = null;

    public function __construct(
        private readonly BuildStage $stage,
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return match ($this->stage) {
            BuildStage::Production => $this->fallback_request_handler?->handle($request) ?? new NotFoundResponse(),
            BuildStage::Development, BuildStage::Staging => $handler->handle($request),
        };
    }

    public function setFallbackRequestHandler(RequestHandlerInterface $handler): void
    {
        $this->fallback_request_handler = $handler;
    }
}
