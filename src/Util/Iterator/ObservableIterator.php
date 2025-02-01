<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Util\Iterator;

use IteratorIterator;
use PhoneBurner\SaltLite\Framework\Util\Helper\Iter;

/**
 * @extends IteratorIterator<mixed, mixed, \Traversable<mixed, mixed>>
 */
class ObservableIterator extends IteratorIterator implements \SplSubject
{
    /**
     * @var array<\SplObserver>
     */
    private array $observers = [];

    /**
     * @param iterable<mixed> $iterable
     */
    public function __construct(iterable $iterable)
    {
        parent::__construct(Iter::cast($iterable));
    }

    #[\Override]
    public function valid(): bool
    {
        if (parent::valid()) {
            $this->notify();
            return true;
        }
        return false;
    }

    #[\Override]
    public function attach(\SplObserver $observer): void
    {
        $this->observers[\spl_object_id($observer)] = $observer;
    }

    #[\Override]
    public function detach(\SplObserver $observer): void
    {
        $key = \spl_object_id($observer);
        if (\array_key_exists($key, $this->observers)) {
            unset($this->observers[$key]);
        }
    }

    #[\Override]
    public function notify(): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
