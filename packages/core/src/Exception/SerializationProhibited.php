<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Exception;

class SerializationProhibited extends \LogicException
{
    public function __construct()
    {
        parent::__construct('Serialization of Objects with Sensitive Parameters is Prohibited');
    }
}
