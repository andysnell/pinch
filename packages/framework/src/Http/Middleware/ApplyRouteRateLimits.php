<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Middleware;

use PhoneBurner\Pinch\Component\Cache\CacheKey;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PhoneBurner\Pinch\Component\Http\Routing\Match\RouteMatch;
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
class ApplyRouteRateLimits implements MiddlewareInterface
{
    public const array DEFAULT_RATE_LIMIT_GROUP_ATTRIBUTES = [
        'ip' => IpAddress::class,
    ];

    public function __construct(
        private readonly RequestRateLimiter $rate_limiter,
        private readonly RequestRateLimits|null $default_rate_limit = null,
        private readonly array $rate_limit_group_attributes = [],
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
        // Check the route have rate limits defined
        $rate_limits = $request->getAttribute(RouteMatch::class)?->getAttributes()[RequestRateLimits::class] ?? null;
        if ($rate_limits instanceof RequestRateLimits) {
            return $rate_limits;
        }

        // Check the request for rate limits (see note above for order reasoning)
        $rate_limits = $request->getAttribute(RequestRateLimits::class);
        if ($rate_limits instanceof RequestRateLimits) {
            return $rate_limits;
        }

        return $this->default_rate_limit;
    }

    private function resolveRateLimitGroup(ServerRequestInterface $request): CacheKey|null
    {
        foreach ($this->rate_limit_group_attributes as $shortname => $attribute) {
            $value = $request->getAttribute($attribute);
            if (CacheKey::check($value)) {
                return new CacheKey(\is_int($shortname) ? $attribute : $shortname, $value);
            }
        }

        return null;
    }
}
