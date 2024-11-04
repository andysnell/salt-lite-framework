<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Domain\Time;

class StopWatch
{
    private readonly int $start;

    private function __construct()
    {
        $this->start = \hrtime(true);
    }

    public static function start(): self
    {
        return new self();
    }

    public function elapsed(): ElapsedTime
    {
        return new ElapsedTime(\hrtime(true) - $this->start);
    }
}
