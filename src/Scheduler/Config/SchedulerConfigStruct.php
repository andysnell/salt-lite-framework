<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Scheduler\Config;

use PhoneBurner\SaltLite\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Scheduler\ScheduleProvider;

class SchedulerConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param list<class-string<ScheduleProvider>> $schedule_providers
     */
    public function __construct(
        public array $schedule_providers = [],
    ) {
    }
}
