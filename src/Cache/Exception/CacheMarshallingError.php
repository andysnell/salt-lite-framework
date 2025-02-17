<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache\Exception;

use PhoneBurner\SaltLite\Framework\Util\Serialization\Exception\SerializationFailure;

class CacheMarshallingError extends SerializationFailure implements CacheException
{
}
