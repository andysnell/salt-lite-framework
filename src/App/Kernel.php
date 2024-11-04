<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\App;

use PhoneBurner\SaltLite\Framework\Util\Attribute\Contract;

/**
 * Kernel instances are responsible for knowing how to run the application,
 * depending on the context, e.g. processing a HTTP request and sending the
 * response versus handling the execution and output of a command line
 * script.
 */
#[Contract]
interface Kernel
{
    public function run(): void;
}
