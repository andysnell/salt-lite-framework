<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

class LongRunningProcessServiceResetter
{
    /**
     * @var \WeakMap<object, string>
     */
    private \WeakMap $service_map;

    public function __construct()
    {
        $this->service_map = new \WeakMap();
    }

    public function add(object $service, string $method): void
    {
        $this->service_map[$service] = $method;
    }

    public function reset(): void
    {
        foreach ($this->service_map as $service => $method) {
            $service->$method();
        }
    }
}
