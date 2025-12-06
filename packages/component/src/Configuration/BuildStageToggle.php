<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

/**
 * @template T
 */
interface BuildStageToggle
{
    /**
     * @return T
     */
    public function __invoke(BuildStage $stage): mixed;
}
