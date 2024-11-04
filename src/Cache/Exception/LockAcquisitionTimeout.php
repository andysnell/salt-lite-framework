<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Exception;

class LockAcquisitionTimeout extends \RuntimeException implements CacheException
{
}
