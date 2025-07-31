<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Http;

// This should trigger an error - component importing framework
use PhoneBurner\Pinch\Framework\Http\HttpServiceProvider;

class TestClass
{
    public function testMethod(): void
    {
        // Usage would also trigger error
        $service_provider = new HttpServiceProvider();
    }
}
