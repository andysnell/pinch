<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String;

// This should trigger an error - core importing component
use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\SignatureKeyPair;
use PhoneBurner\Pinch\Component\EmailAddress\EmailAddress;

class TestClass
{
    public function testMethod(): void
    {
        // This should trigger an error - core instantiating component
        $kp = SignatureKeyPair::generate();
        $id = new EmailAddress('jdoe@example.com');
    }
}
