<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\MessageBus;

use PhoneBurner\SaltLite\Framework\MessageBus\Config\TransportConfigStruct;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface TransportFactory
{
    public function make(TransportConfigStruct $config): TransportInterface;
}
