<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Console;

class ConsoleApplicationFactory
{
    public function make(): ConsoleApplication
    {
        return new ConsoleApplication();
    }
}
