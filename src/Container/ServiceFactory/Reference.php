<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Container\ServiceFactory;

use PhoneBurner\SaltLite\Framework\App\App;
use PhoneBurner\SaltLite\Framework\Container\ServiceFactory\ServiceFactory;

/**
 * Factory class for binding an id (e.g. interface) to an entry in the container
 *(entry_id).
 */
readonly class Reference implements ServiceFactory
{
    public function __construct(public string $entry_id)
    {
    }

    public function __invoke(App $app): object
    {
        return $app->get($this->entry_id);
    }
}
