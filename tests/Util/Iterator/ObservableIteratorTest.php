<?php

declare(strict_types=1);

namespace PhoneBurner\SaltLite\Framework\Tests\Util\Iterator;

use PhoneBurner\SaltLite\Framework\Util\Iterator\ObservableIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ObservableIteratorTest extends TestCase
{
    #[Test]
    public function getIterator_notifies_observers_on_each_iteration(): void
    {
        $foo = ['foo' => 2343, 'bar' => 23, 'baz' => 32];
        $observer = self::getObserver();
        $sut = new ObservableIterator($foo);
        $sut->attach($observer);

        $counter = 0;
        foreach ($sut as $value) {
            ++$counter;
        }

        self::assertSame(3, $observer->counter);
        self::assertSame([
            ['key' => 'foo', 'value' => 2343],
            ['key' => 'bar', 'value' => 23],
            ['key' => 'baz', 'value' => 32],
        ], $observer->updated);
    }

    #[Test]
    public function getIterator_does_empty_case(): void
    {
        $foo = [];
        $observer = self::getObserver();
        $sut = new ObservableIterator($foo);
        $sut->attach($observer);

        $counter = 0;
        foreach ($sut as $value) {
            ++$counter;
        }

        self::assertSame(0, $observer->counter);
        self::assertSame([], $observer->updated);
    }

    /**
     * @return \SplObserver&object{updated: array<array{key: string, value: int}>,counter: int}
     */
    protected static function getObserver(): \SplObserver
    {
        return new class implements \SplObserver {
            /**
             * @var array<array{key: string, value: int}>
             */
            public array $updated = [];

            public int $counter = 0;

            public function update(\SplSubject $subject): void
            {
                ++$this->counter;
                \assert($subject instanceof \Iterator);
                $this->updated[] = ['key' => $subject->key(), 'value' => $subject->current()];
            }
        };
    }
}
