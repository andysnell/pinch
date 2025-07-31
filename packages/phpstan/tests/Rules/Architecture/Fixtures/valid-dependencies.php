<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Http;

use PhoneBurner\Pinch\Component\Http\Response\StreamResponse;
use PhoneBurner\Pinch\String\RegExp;

class TestClass
{
    public function testMethod(): void
    {
        // Valid instantiations
        $request = new StreamResponse();
        $regex = new RegExp('test');
    }
}
