<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Tests\Iterator;

use PhoneBurner\Pinch\Iterator\ObservableIterator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ObservableIteratorTest extends TestCase
{
    #[Test]
    public function getIteratorNotifiesObserversOnEachIteration(): void
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
    public function detachRemovesObservers(): void
    {
        $foo = ['foo' => 2343, 'bar' => 23, 'baz' => 32, 'qux' => 42];

        $observer_1 = self::getObserver();
        $observer_2 = self::getObserver();

        $sut = new ObservableIterator($foo);
        $sut->attach($observer_1);
        $sut->attach($observer_2);

        $counter = 0;
        foreach ($sut as $value) {
            ++$counter;
            if ($counter === 2) {
                $sut->detach($observer_1);
            }
        }

        self::assertSame(2, $observer_1->counter);
        self::assertSame([
            ['key' => 'foo', 'value' => 2343],
            ['key' => 'bar', 'value' => 23],
        ], $observer_1->updated);

        self::assertSame(4, $observer_2->counter);
        self::assertSame([
            ['key' => 'foo', 'value' => 2343],
            ['key' => 'bar', 'value' => 23],
            ['key' => 'baz', 'value' => 32],
            ['key' => 'qux', 'value' => 42],
        ], $observer_2->updated);
    }

    #[Test]
    public function getIteratorDoesEmptyCase(): void
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

    #[Test]
    public function constructorAcceptsArray(): void
    {
        $array = ['test' => 123];
        $observable = new ObservableIterator($array);

        self::assertInstanceOf(ObservableIterator::class, $observable);
        self::assertSame(['test' => 123], \iterator_to_array($observable));
    }

    #[Test]
    public function constructorAcceptsIterator(): void
    {
        $iterator = new \ArrayIterator(['key' => 'value']);
        $observable = new ObservableIterator($iterator);

        self::assertInstanceOf(ObservableIterator::class, $observable);
        self::assertSame(['key' => 'value'], \iterator_to_array($observable));
    }

    #[Test]
    public function constructorAcceptsGenerator(): void
    {
        $generator = (function () {
            yield 'gen' => 'value';
        })();
        $observable = new ObservableIterator($generator);

        self::assertInstanceOf(ObservableIterator::class, $observable);
        self::assertSame(['gen' => 'value'], \iterator_to_array($observable));
    }

    #[Test]
    public function multipleObserversReceiveNotifications(): void
    {
        $data = ['a' => 1, 'b' => 2];
        $observer1 = self::getObserver();
        $observer2 = self::getObserver();

        $sut = new ObservableIterator($data);
        $sut->attach($observer1);
        $sut->attach($observer2);

        foreach ($sut as $value) {
            // Just iterate
        }

        // Both observers should receive all notifications
        self::assertSame(2, $observer1->counter);
        self::assertSame(2, $observer2->counter);
        self::assertSame($observer1->updated, $observer2->updated);
    }

    #[Test]
    public function detachNonExistentObserverHasNoEffect(): void
    {
        $data = ['x' => 100];
        $observer = self::getObserver();
        $non_attached_observer = self::getObserver();

        $sut = new ObservableIterator($data);
        $sut->attach($observer);
        $sut->detach($non_attached_observer); // This should not cause any issues

        foreach ($sut as $value) {
            // Just iterate
        }

        self::assertSame(1, $observer->counter);
        self::assertSame(0, $non_attached_observer->counter);
    }

    #[Test]
    public function notifyIsCalledOnEachValidCall(): void
    {
        $data = ['first' => 10, 'second' => 20];
        $observer = self::getObserver();

        $sut = new ObservableIterator($data);
        $sut->attach($observer);

        // Manually test the iterator protocol
        $sut->rewind();
        self::assertTrue($sut->valid()); // Should notify
        self::assertSame(1, $observer->counter);

        $sut->next();
        self::assertTrue($sut->valid()); // Should notify again
        self::assertSame(2, $observer->counter);

        $sut->next();
        self::assertFalse($sut->valid()); // Should not notify since invalid
        self::assertSame(2, $observer->counter); // Counter unchanged
    }

    #[Test]
    public function rewindResetIteratorCorrectly(): void
    {
        $data = ['test' => 42];
        $observer = self::getObserver();

        $sut = new ObservableIterator($data);
        $sut->attach($observer);

        // First iteration
        foreach ($sut as $key => $value) {
            self::assertSame('test', $key);
            self::assertSame(42, $value);
        }

        self::assertSame(1, $observer->counter);

        // Second iteration after implicit rewind
        foreach ($sut as $key => $value) {
            self::assertSame('test', $key);
            self::assertSame(42, $value);
        }

        // Should have notified twice (once per iteration)
        self::assertSame(2, $observer->counter);
    }

    #[Test]
    public function attachSameObserverMultipleTimesOnlyNotifiesOnce(): void
    {
        $data = ['single' => 1];
        $observer = self::getObserver();

        $sut = new ObservableIterator($data);
        $sut->attach($observer);
        $sut->attach($observer); // Attach same observer again

        foreach ($sut as $value) {
            // Just iterate
        }

        // Should only be notified once despite being attached twice
        // (SplObjectStorage behavior)
        self::assertSame(1, $observer->counter);
    }

    /**
     * @return \SplObserver&object{updated: array<array{key: string, value: int}>,counter: int}
     */
    private static function getObserver(): \SplObserver
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
                $key = $subject->key();
                $current = $subject->current();
                \assert(\is_string($key) && \is_int($current));
                $this->updated[] = ['key' => $key, 'value' => $current];
            }
        };
    }
}
