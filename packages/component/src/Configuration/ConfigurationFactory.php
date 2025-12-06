<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Configuration;

/**
 * Important: for the sake of serializing the configuration as a PHP array, and
 * leveraging the performance we can get out of opcache keeping that static array
 * in memory, the values of the configuration MUST be limited to scalar types,
 * null, PHP enum cases (since those are just fancy class constants
 *  under the hood), and simple struct-like classes implementing arrays.
 */
interface ConfigurationFactory
{
    public const string DEFAULT_CONFIG_PATH = '/config';

    public const string DEFAULT_CACHE_FILE_TEMPLATE = '/storage/bootstrap/config.%s.cache.php';

    /**
     * @param string $config_dir_path relative to the root defined in $environment
     * @param string $cache_file_path_template relative to the root defined in $environment with %s replaced by the context name
     */
    public function make(
        Environment $environment,
        string $config_dir_path = self::DEFAULT_CONFIG_PATH,
        string $cache_file_path_template = self::DEFAULT_CACHE_FILE_TEMPLATE,
    ): Configuration;
}
