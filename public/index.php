<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\App\Context;
use PhoneBurner\SaltLite\App\Kernel;
use PhoneBurner\SaltLite\Framework\App\App;

require_once __DIR__ . '/../vendor/autoload.php';

App::exec(Context::Http, static function (App $app): void {
    $app->get(Kernel::class)->run();
});
