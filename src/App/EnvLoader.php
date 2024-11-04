<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use josegonzalez\Dotenv\Loader;

class EnvLoader
{
    public static function override(string $env_file): void
    {
        if (! \file_exists($env_file)) {
            return;
        }

        Loader::load([
            'filepath' => $env_file,
            'toEnv' => true,
            'putenv' => true,
        ]);
    }
}
