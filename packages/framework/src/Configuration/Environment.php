<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Framework\Configuration;

use PhoneBurner\Pinch\Component\Configuration\BuildStage;
use PhoneBurner\Pinch\Component\Configuration\BuildStageToggle;
use PhoneBurner\Pinch\Component\Configuration\Context;
use PhoneBurner\Pinch\Component\Configuration\Environment as EnvironmentContract;

use function PhoneBurner\Pinch\Type\filter_ini;

/**
 * Represents the environment and context in which the application is running.
 * This class is a container that that holds server and environment variables.
 * (The server is checked first before the environment.) It also defines some
 * methods for getting things like the root directory and hostname.
 */
final class Environment implements EnvironmentContract
{
    // phpcs:disable
    public string $hostname {
        get => \gethostname() ?: 'localhost';
    }
    // phpcs:enable

    /**
     * @param array<string, mixed> $server Since this will usually be $_SERVER, it cannot be readonly
     * @param array<string, mixed> $env Since this will usually be $_ENV, it cannot be readonly
     */
    public function __construct(
        public readonly Context $context,
        public readonly BuildStage $stage,
        public readonly string $root,
        private array &$server,
        private array &$env,
    ) {
    }

    public function has(string $id): bool
    {
        return isset($this->server[$id]) || isset($this->env[$id]);
    }

    public function get(string $id): mixed
    {
        return $this->server[$id] ?? $this->env[$id] ?? null;
    }

    public function server(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $staging = null,
    ): \UnitEnum|string|int|float|bool|null {
        return filter_ini($this->server[$key] ?? null) ?? match ($this->stage) {
            BuildStage::Production => $production,
            BuildStage::Staging => $staging ?? $production,
            BuildStage::Development => $development ?? $staging ?? $production,
        };
    }

    public function env(
        string $key,
        mixed $production = null,
        mixed $development = null,
        mixed $staging = null,
    ): \UnitEnum|string|int|float|bool|null {
        return filter_ini($this->env[$key] ?? null) ?? match ($this->stage) {
            BuildStage::Production => $production,
            BuildStage::Staging => $staging ?? $production,
            BuildStage::Development => $development ?? $staging ?? $production,
        };
    }

    /**
     * @param BuildStageToggle<mixed>|null $toggle
     * @return mixed
     */
    public function raw(string $key, BuildStageToggle|null $toggle = null): mixed
    {
        return $this->env[$key] ?? ($toggle)($this->stage);
    }

    public function match(mixed $production, mixed $development = null, mixed $staging = null): mixed
    {
        return match ($this->stage) {
            BuildStage::Production => $production,
            BuildStage::Development => $development ?? $production,
            BuildStage::Staging => $staging ?? $development ?? $production,
        };
    }
}
