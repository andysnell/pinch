<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Routing\FastRoute;

use FastRoute\Dispatcher;
use PhoneBurner\Pinch\Attribute\Usage\Internal;
use PhoneBurner\Pinch\Component\Http\Domain\HttpMethod;
use PhoneBurner\Pinch\Component\Http\Routing\Result\MethodNotAllowed;
use PhoneBurner\Pinch\Component\Http\Routing\Result\RouteFound;
use PhoneBurner\Pinch\Component\Http\Routing\Result\RouteNotFound;
use PhoneBurner\Pinch\Component\Http\Routing\RouterResult;
use PhoneBurner\Pinch\Framework\Http\Routing\FastRoute\FastRouteMatch;

#[Internal]
class FastRouteResultFactory
{
    public function make(FastRouteMatch $match): RouterResult
    {
        return match ($match->getStatus()) {
            Dispatcher::METHOD_NOT_ALLOWED => new MethodNotAllowed(
                ...\array_map(HttpMethod::instance(...), $match->getMethods()),
            ),
            Dispatcher::FOUND => new RouteFound(
                definition: \unserialize($match->getRouteData(), ['allowed_classes' => true]),
                path_parameters: $match->getPathVars(),
            ),
            default => new RouteNotFound(),
        };
    }
}
