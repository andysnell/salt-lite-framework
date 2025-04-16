<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App\ErrorHandling;

interface ErrorHandler
{
    public function __invoke(int $level, string $message, string $file, int $line): bool;
}
