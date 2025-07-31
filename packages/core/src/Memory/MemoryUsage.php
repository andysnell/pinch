<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Memory;

use PhoneBurner\Pinch\Memory\Bytes;

class MemoryUsage
{
    public static function current(bool $system_allocated = false): Bytes
    {
        return new Bytes(\memory_get_usage($system_allocated));
    }

    public static function peak(bool $system_allocated = false): Bytes
    {
        return new Bytes(\memory_get_peak_usage($system_allocated));
    }

    public function reset(bool $system_allocated = false): Bytes
    {
        \memory_reset_peak_usage();
        return new Bytes(\memory_get_peak_usage($system_allocated));
    }
}
