<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Configuration;

use Brick\VarExporter\VarExporter;
use PhoneBurner\SaltLite\Framework\App\BuildStage;
use PhoneBurner\SaltLite\Framework\App\Environment;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileWriter;

class ConfigurationFactory
{
    private const int EXPORT_OPTIONS = VarExporter::ADD_RETURN | VarExporter::TRAILING_COMMA_IN_ARRAY;
    private const string CONFIG_PATH = '/config';
    private const string CACHE_FILE = '/storage/bootstrap/config.cache.php';

    public static function make(Environment $environment): ImmutableConfiguration
    {
        $use_cache = $environment->stage !== BuildStage::Development || $_ENV['SALT_ENABLE_CONFIG_CACHE'];
        $cache_file = $environment->root() . self::CACHE_FILE;
        if (\file_exists($cache_file)) {
            if ($use_cache) {
                /** @phpstan-ignore include.fileNotFound (see https://github.com/phpstan/phpstan/issues/11798) */
                return new ImmutableConfiguration(include $cache_file);
            }

            // remove stale cache file to force re-creation next time the cache is enabled
            @\unlink($cache_file);
        }

        $config = [];
        foreach (\glob($environment->root() . self::CONFIG_PATH . '/*.php') ?: [] as $file) {
            foreach (include $file ?: [] as $key => $value) {
                $config[$key] = $value;
            }
        }

        if ($use_cache) {
            FileWriter::string($cache_file, '<?php ' . VarExporter::export($config, self::EXPORT_OPTIONS));
        }

        return new ImmutableConfiguration($config);
    }
}
