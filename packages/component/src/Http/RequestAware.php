<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\RequestInterface;

interface RequestAware
{
    // phpcs:ignore
    public RequestInterface $request { get; }
}
