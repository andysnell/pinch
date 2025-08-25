<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\Domain\HttpHeader;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimiter;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitGroup;
use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimits;
use PhoneBurner\Pinch\Component\Http\Response\Exceptional\TooManyRequestsResponse;
use PhoneBurner\Pinch\Time\Clock\Clock;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Note: this middleware must go AFTER the route is matched.
 *
 * Rate limits defined on individual routes trump rate limits resolved from the
 * request as an attribute (e.g., ones set from authenticating the user). This is
 * because some routes may be highly sensitive to traffic or the opposite -- act
 * as firehose endpoints that want to be as efficient as possible in handling the
 * request.
 */
class ApplyGlobalRateLimitPolicies implements MiddlewareInterface
{
    public function __construct(
        private readonly Clock $clock,
        private readonly RequestRateLimiter $rate_limiter,
        private readonly RequestRateLimits|null $default_rate_limit = null,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rate_limits = $this->resolveRateLimitPolicies($request);
        if ($rate_limits === null) {
            return $handler->handle($request);
        }

        $rate_limit_group = $this->resolveRateLimitGroup($request);
        if ($rate_limit_group === null) {
            return $handler->handle($request);
        }

        $datetime = $this->clock->now();
        $result = $this->rate_limiter->throttle($rate_limit_group, $rate_limits);
        if ($result->allowed) {
            return $handler->handle($request)
                ->withAddedHeader(HttpHeader::RATELIMIT_POLICY, $result->policies())
                ->withAddedHeader(HttpHeader::RATELIMIT, $result->limit($datetime));
        }

        return new TooManyRequestsResponse(headers: [
            HttpHeader::RATELIMIT_POLICY => $result->policies(),
            HttpHeader::RATELIMIT => $result->limit($datetime),
            HttpHeader::RETRY_AFTER => $result->retry($datetime),
        ]);
    }

    private function resolveRateLimitPolicies(ServerRequestInterface $request): RequestRateLimits|null
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
