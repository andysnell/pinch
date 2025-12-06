<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

use PhoneBurner\Pinch\Component\Configuration\BuildStage;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;

/**
 * @phpstan-require-implements BuildStageToggle
 */
trait TogglesWithStandardFallbackBehavior
{
    public function __invoke(BuildStage $stage): mixed
    {
        return match ($stage) {
            BuildStage::Production => $this->production,
            BuildStage::Staging => $this->staging ?? $this->production,
            BuildStage::Development => $this->development ?? $this->staging ?? $this->production,
        };
    }
}
