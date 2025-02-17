<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Http\Session;

use PhoneBurner\SaltLite\Framework\Domain\PhpSerializable;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\KeyValueStore;
use PhoneBurner\SaltLite\Framework\Util\Collections\Map\MapWrapper;
use PhoneBurner\SaltLite\Framework\Util\Collections\MapCollection;

/**
 * Note: our Session object intentionally does not support dot notation for
 * retrieving nested values. It is designed for a more flat key-value approach.
 * Because the session is read and written every request it is enabled for, it
 * is important to keep the overall size of the session data small. Ideally, a
 * session payload will have not much more than the CSRF token, some
 * authentication/authorization data, and key/values directly pertaining to
 * rendering the user's UI experience (e.g. dark vs light mode, shopping cart).
 *
 * Consider using alternative persistence methods for more complex data storage.
 *
 * @implements MapCollection<mixed>
 * @implements PhpSerializable<array{csrf_token: CsrfToken, attributes: KeyValueStore, flash_data: list<string>}>
 */
final class SessionData implements MapCollection, PhpSerializable
{
    use MapWrapper;

    private CsrfToken $csrf_token;

    private readonly KeyValueStore $attributes;

    private array $new_flash_data = [];
    private array $old_flash_data = [];

    public function __construct()
    {
        $this->csrf_token = CsrfToken::generate();
        $this->attributes = new KeyValueStore();
    }

    public function regenerateCsrfToken(): CsrfToken
    {
        return $this->csrf_token = CsrfToken::generate();
    }

    public function csrf(): CsrfToken
    {
        return $this->csrf_token;
    }

    public function clear(): void
    {
        $this->regenerateCsrfToken();
        $this->attributes->clear();
    }

    /**
     * Set a "flash" key/value pair that will be available as part of the session
     * data for this and the next request only (unless the keep() or reflash() methods
     * are called to persist the key/value longer).
     */
    public function flash(\Stringable|string $key, mixed $value): void
    {
        $this->set($key, $value);
        $this->keep($key);
    }

    /**
     * Keep any flash data set in the previous request around for the next request.
     */
    public function reflash(): void
    {
        $this->keep(...\array_keys($this->old_flash_data));
    }

    /**
     * Keep only the specified flash data (by key) for the next request.
     */
    public function keep(\Stringable|string ...$keys): void
    {
        foreach ($keys as $key) {
            $this->new_flash_data[(string)$key] = 1;
            unset($this->old_flash_data[(string)$key]);
        }
    }

    /**
     * Prepare the session data for serializiation and writing to the handler.
     * Remove old flash data that was set in the previous request that was not
     * otherwise kept for future requests. Make the new flash data the old flash
     * data, and reset the new flash data to an empty array.
     */
    public function preserialize(): self
    {
        \array_map($this->unset(...), \array_keys($this->old_flash_data));
        $this->old_flash_data = $this->new_flash_data;
        $this->new_flash_data = [];
        return $this;
    }

    public function __serialize(): array
    {
        return [
            'csrf_token' => $this->csrf_token,
            'attributes' => $this->attributes,
            'flash_data' => \array_keys($this->old_flash_data),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->csrf_token = $data['csrf_token'];
        $this->attributes = $data['attributes'];
        $this->old_flash_data = \array_fill_keys($data['flash_data'], 1);
        $this->new_flash_data = [];
    }

    protected function wrapped(): KeyValueStore
    {
        return $this->attributes;
    }
}
