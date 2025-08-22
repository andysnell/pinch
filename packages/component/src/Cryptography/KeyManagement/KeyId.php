<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Cryptography\KeyManagement;

use PhoneBurner\Pinch\Component\Cryptography\Asymmetric\PublicKey;

class KeyId
{
    public function __construct(public PublicKey $public_key)
    {
    }

    public static function ofKey(PublicKey $public_key): self
    {
        return new self($public_key);
    }
}
