<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\PhoneNumber;

/**
 * Helper class to provide a static method that can be referenced as a first
 * class callable when performing the common operation of casting an array of
 * phone number objects to their e164 string representations.
 */
class PhoneNumberExtractor
{
    public static function string(PhoneNumber $number): string
    {
        return (string)$number->toE164();
    }
}
