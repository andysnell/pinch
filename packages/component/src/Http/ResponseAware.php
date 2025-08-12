<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

use Psr\Http\Message\ResponseInterface;

interface ResponseAware
{
    // phpcs:ignore
    public ResponseInterface $response { get; }
}
