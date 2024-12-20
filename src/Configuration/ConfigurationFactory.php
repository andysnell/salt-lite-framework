<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Configuration;

use Brick\VarExporter\VarExporter;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileWriter;

class ConfigurationFactory
{
    private const int EXPORT_OPTIONS = VarExporter::ADD_RETURN | VarExporter::TRAILING_COMMA_IN_ARRAY;
    private const string CONFIG_PATH = '/config';
    private const string CACHE_FILE = '/storage/bootstrap/config.cache.php';

    public static function make(Environment $environment): ImmutableConfiguration
    {
        $cache_enabled = $environment->env('SALT_ENABLE_CONFIG_CACHE', true, false);
        $cache_file = $environment->root() . self::CACHE_FILE;

        if ($cache_enabled && \file_exists($cache_file)) {
            /** @phpstan-ignore include.fileNotFound (see https://github.com/phpstan/phpstan/issues/11798) */
            return new ImmutableConfiguration(include $cache_file);
        }

        $config = [];
        foreach (\glob($environment->root() . self::CONFIG_PATH . '/*.php') ?: [] as $file) {
            foreach (include $file ?: [] as $key => $value) {
                $config[$key] = $value;
            }
        }

        if ($cache_enabled) {
            FileWriter::string($cache_file, '<?php ' . VarExporter::export($config, self::EXPORT_OPTIONS));
        }

        return new ImmutableConfiguration($config);
    }
}
