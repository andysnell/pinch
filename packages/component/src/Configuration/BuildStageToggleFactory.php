<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\ArrayBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\BoolBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\FloatBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\IntBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\MixedBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\NumberBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\ObjectBuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle\StringBuildStageToggle;

class BuildStageToggleFactory
{
    /**
     * @template T of array
     * @param T $production
     * @param T $staging
     * @param T $development
     * @return ArrayBuildStageToggle<T>
     */
    public static function array(
        array $production = [],
        array $staging = [],
        array $development = [],
    ): ArrayBuildStageToggle {
        return new ArrayBuildStageToggle($production, $staging, $development);
    }

    public static function bool(
        bool $production = false,
        bool $staging = false,
        bool $development = false,
    ): BoolBuildStageToggle {
        return new BoolBuildStageToggle($production, $staging, $development);
    }

    public static function int(
        int $production = 0,
        int $staging = 0,
        int $development = 0,
    ): IntBuildStageToggle {
        return new IntBuildStageToggle($production, $staging, $development);
    }

    public static function float(
        float $production = 0.0,
        float $staging = 0.0,
        float $development = 0.0,
    ): FloatBuildStageToggle {
        return new FloatBuildStageToggle($production, $staging, $development);
    }

    public static function mixed(
        mixed $production = null,
        mixed $staging = null,
        mixed $development = null,
    ): MixedBuildStageToggle {
        return new MixedBuildStageToggle($production, $staging, $development);
    }

    public static function number(
        int|float $production = 0,
        int|float $staging = 0,
        int|float $development = 0,
    ): NumberBuildStageToggle {
        return new NumberBuildStageToggle($production, $staging, $development);
    }

    /**
     * @template T of object
     * @param T $production
     * @param T $staging
     * @param T $development
     * @return ObjectBuildStageToggle<T>
     */
    public static function object(
        object $production,
        object $staging,
        object $development,
    ): ObjectBuildStageToggle {
        return new ObjectBuildStageToggle($production, $staging, $development);
    }

    // string
    public static function string(
        string $production = '',
        string $staging = '',
        string $development = '',
    ): StringBuildStageToggle {
        return new StringBuildStageToggle($production, $staging, $development);
    }
}
