<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\String\Encoding;

class Base64Url
{
    public static function encode(string $value, bool $padding = true): string
    {
        $encoded = \sodium_bin2base64($value, \SODIUM_BASE64_VARIANT_URLSAFE);
        return $padding ? $encoded : \rtrim($encoded, '=');
    }

    public static function decode(string $value): string
    {
        return \sodium_base642bin(\rtrim($value, '='), \SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    public function validate(string $value, bool $strict = false): bool
    {
        // Check if the string is valid Base64
        if (\preg_match('/^[A-Za-z0-9_\-]*={0,2}$/', $value) !== 1) {
            return false;
        }

        // Check correct padding
        if ($strict) {
            $length = \strlen(\rtrim($value, '='));
            return \strlen($value) - $length === (4 - $length % 4) % 4;
        }

        return true;
    }
}
