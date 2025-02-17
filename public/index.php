<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\App\Context;
use PhoneBurner\SaltLite\Framework\App\Kernel;

require_once __DIR__ . '/../vendor/autoload.php';

App::exec(Context::Http, static function (App $app): void {
    $app->get(Kernel::class)->run();
});
