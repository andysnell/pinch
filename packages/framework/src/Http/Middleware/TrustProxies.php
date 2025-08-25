<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http\Middleware;

use PhoneBurner\Pinch\Component\Http\RateLimiter\RequestRateLimitGroup;
use PhoneBurner\Pinch\Component\I18n\Region\Region;
use PhoneBurner\Pinch\Component\IpAddress\IpAddress;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function PhoneBurner\Pinch\Array\array_first;

class TrustProxies implements MiddlewareInterface
{
    public function __construct(
        private bool $trust_all_proxies = true,
        private bool $trust_true_client_ip_header = true,
        private bool $trust_cf_ip_country_header = true,
    ) {
    }

    private function resolveClientIpCountry(ServerRequestInterface $request): ServerRequestInterface
    {
        $server_params = $request->getServerParams();
        if ($this->trust_cf_ip_country_header === false || ! isset($server_params['HTTP_CF_IPCOUNTRY'])) {
            return $request;
        }

        return $request->withAttribute(Region::class, Region::cast($server_params['HTTP_CF_IPCOUNTRY']) ?? Region::ZZ);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->resolveClientIpAddress($request);
        $request = $this->resolveClientIpCountry($request);

        return $handler->handle($request);
    }

    /**
     * We're only going to check the HTTP_TRUE_CLIENT_IP and HTTP_X_FORWARDED_FOR
     * headers, since the REMOTE_HOST is the default set in the request factory.
     *
     * We only override that default if a) the header is allowed by configuration,
     * b) it's a valid IP address, and c) it is not the same as the existing one.
     *
     * If we update the IP address, we also update the default rate limit group
     */
    private function resolveClientIpAddress(ServerRequestInterface $request): ServerRequestInterface
    {
        $addresses = $this->resolveIpAddressString($request);
        if ($addresses === '') {
            return $request;
        }

        $ip_address = IpAddress::tryFrom(\trim((string)array_first(\explode(',', $addresses))));
        if ($ip_address !== null && $ip_address->value !== $request->getAttribute(IpAddress::class)?->value) {
            $request = $request->withAttribute(IpAddress::class, $ip_address);
            $request = $request->withAttribute(RequestRateLimitGroup::class, RequestRateLimitGroup::fromIpAddress($ip_address));
        }

        return $request;
    }

    private function resolveIpAddressString(ServerRequestInterface $request): string
    {
        $server_params = $request->getServerParams();
        if ($this->trust_true_client_ip_header && isset($server_params['HTTP_TRUE_CLIENT_IP'])) {
            return $server_params['HTTP_TRUE_CLIENT_IP'];
        }

        if ($this->trust_all_proxies && isset($server_params['HTTP_X_FORWARDED_FOR'])) {
            return $server_params['HTTP_X_FORWARDED_FOR'];
        }

        return '';
    }
}
