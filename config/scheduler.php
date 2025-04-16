<?php

declare(strict_types=1);

use PhoneBurner\SaltLite\Framework\Scheduler\Config\SchedulerConfigStruct;

return [
    'scheduler' => new SchedulerConfigStruct(
        schedule_providers: [
            // 'default' => \PhoneBurner\SaltLite\Framework\Scheduler\ApplicationScheduleProvider::class,
        ],
    ),
];
