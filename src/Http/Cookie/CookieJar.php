<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Cookie;

use PhoneBurner\SaltLite\Framework\Util\Collections\Map\GenericMapCollection;

/**
 * @extends GenericMapCollection<Cookie>
 */
class CookieJar extends GenericMapCollection
{
    public function add(Cookie $cookie): self
    {
        $this->set($cookie->name, $cookie);
        return $this;
    }

    public function remove(string $name): self
    {
        $this->set($name, Cookie::remove($name));
        return $this;
    }
}
