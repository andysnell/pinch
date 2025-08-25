<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitGroup;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Note: this middleware must go AFTER the route is matched.
 *
 * Rate limits defined on individual routes trump rate limits resolved from the
 * request as an attribute (e.g. ones set from authenticating the user). This is
 * because some routes may be highly sensitive to traffic or the opposite -- act
 * as firehose endpoints that want to be as efficient as possible in handling the
 * request.
 */
class ApplyGlobalRateLimits implements MiddlewareInterface
{
    public const array DEFAULT_RATE_LIMIT_GROUP_ATTRIBUTES = [
        'ip' => IpAddress::class,
    ];

    public function __construct(
        private readonly RequestRateLimiter $rate_limiter,
        private readonly RequestRateLimits|null $default_rate_limit = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rate_limits = $this->resolveRateLimits($request);
        if ($rate_limits === null) {
            return $handler->handle($request);
        }

        $rate_limit_group = $this->resolveRateLimitGroup($request);
        if ($rate_limit_group === null) {
            return $handler->handle($request);
        }

        return $handler->handle($request);
    }

    private function resolveRateLimits(ServerRequestInterface $request): RequestRateLimits|null
    {
        $rate_limits = $request->getAttribute(RequestRateLimits::class);
        if ($rate_limits instanceof RequestRateLimits) {
            return $rate_limits;
        }

        if ($rate_limits !== null) {
            throw new \LogicException(
                \sprintf('Rate Limit Misconfiguration: got %s', \get_debug_type($rate_limits)),
            );
        }

        return $this->default_rate_limit;
    }

    private function resolveRateLimitGroup(ServerRequestInterface $request): RequestRateLimitGroup|null
    {
        $rate_limit_group = $request->getAttribute(RequestRateLimitGroup::class);
        if ($rate_limit_group instanceof RequestRateLimitGroup) {
            return $rate_limit_group;
        }

        if ($rate_limit_group !== null) {
            throw new \LogicException(
                \sprintf('Rate Limit Group Misconfiguration: got %s', \get_debug_type($rate_limit_group)),
            );
        }

        return null;
    }
}
