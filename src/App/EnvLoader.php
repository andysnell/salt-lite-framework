<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use Brick\VarExporter\VarExporter;
use josegonzalez\Dotenv\Loader;
use PhoneBurner\SaltLite\Framework\Util\Filesystem\FileWriter;

class EnvLoader
{
    private const int EXPORT_OPTIONS = VarExporter::ADD_RETURN | VarExporter::TRAILING_COMMA_IN_ARRAY;

    private const string CACHE_FILE = '/storage/bootstrap/env.cache.php';

    public static function override(BuildStage $stage, string $app_root): void
    {
        foreach (self::load($stage, $app_root) as $key => $value) {
            $_ENV[$key] = $value;
        }
    }

    private static function load(BuildStage $stage, string $app_root): array
    {
        $cache_file = $app_root . self::CACHE_FILE;
        if ($stage !== BuildStage::Development && \file_exists($cache_file)) {
            return include $cache_file;
        }

        // Load environment variables from default .env files like ".env.production"
        // first, before overriding from .env
        $default_file = $app_root . '/.env.' . $stage->value;
        $override_file = $app_root . '/.env';

        $env = [
            ...(\file_exists($default_file) ? (new Loader([$default_file])->parse()->toArray()) : []),
            ...(\file_exists($override_file) ? (new Loader([$override_file])->parse()->toArray()) : []),
        ];

        FileWriter::string($cache_file, '<?php ' . VarExporter::export($env, self::EXPORT_OPTIONS));

        return $env;
    }
}
