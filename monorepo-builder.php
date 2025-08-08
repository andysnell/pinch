<?php

declare(strict_types=1);

use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Merge\JsonSchema;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;

return static function (MBConfig $config): void {
    $config->packageDirectories([__DIR__ . '/packages']);
    $config->defaultBranch('main');
    $config->composerSectionOrder(JsonSchema::getProperties());
    $config->packageAliasFormat('<major>.<minor>.x-dev');
    $config->workers([
        SetCurrentMutualDependenciesReleaseWorker::class,
    ]);
};
