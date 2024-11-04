<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class InMemoryCache extends CacheAdapter
{
    public function __construct(CacheItemPoolInterface $cache_item_pool = new ArrayAdapter(storeSerialized: false))
    {
        parent::__construct($cache_item_pool);
    }
}
