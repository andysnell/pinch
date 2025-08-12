<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http\Event;

use PhoneBurner\Pinch\Component\Http\ResponseAware;
use Psr\Http\Message\ResponseInterface;

final class EmittingHttpResponseComplete implements ResponseAware
{
    public function __construct(public ResponseInterface $response)
    {
    }
}
