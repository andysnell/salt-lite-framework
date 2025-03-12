<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Database\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;

use const PhoneBurner\SaltLite\Framework\APP_ROOT;

final readonly class DoctrineMigrationsConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    public function __construct(
        public array $table_storage = [
            'table_name' => 'doctrine_migration_versions',
        ],
        public array $migrations_paths = [
            'PhoneBurner\SaltLite\Migrations' => APP_ROOT . '/src/Database/Migrations',
        ],
    ) {
    }
}
