<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\EventDispatcher\Config;

use PhoneBurner\SaltLite\Framework\App\Configuration\ConfigStruct;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructArrayAccess;
use PhoneBurner\SaltLite\Framework\App\Configuration\Struct\ConfigStructSerialization;
use PhoneBurner\SaltLite\Framework\Logging\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EventDispatcherConfigStruct implements ConfigStruct
{
    use ConfigStructArrayAccess;
    use ConfigStructSerialization;

    /**
     * @param LogLevel|null $event_dispatch_log_level set to null to disable logging
     * @param LogLevel|null $event_failure_log_level set to null to disable logging
     * @param array<class-string, list<class-string>> $listeners
     * @param list<class-string<EventSubscriberInterface>> $subscribers
     */
    public function __construct(
        public LogLevel|null $event_dispatch_log_level = LogLevel::Debug,
        public LogLevel|null $event_failure_log_level = LogLevel::Warning,
        public array $listeners = [],
        public array $subscribers = [],
    ) {
    }
}
