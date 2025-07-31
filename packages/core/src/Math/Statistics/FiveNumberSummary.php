<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Statistics;

use PhoneBurner\Pinch\Iterator\Sort\Sort;

class FiveNumberSummary
{
    // phpcs:disable
    public int|float $min {
        get => $this->values[0];
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $first_quartile {
        get => $this->percentile(0.25);
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $median {
        get => $this->percentile(0.50);
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $third_quartile {
        get => $this->percentile(0.75);
    }
    // phpcs:enable

    // phpcs:disable
    public int|float $max {
        get => $this->values[$this->count - 1];
    }
    // phpcs:enable

    /**
     * @var float[]|int[]
     */
    private readonly array $values;

    /**
     * @var int<1,max>
     */
    public readonly int $count;

    /**
     * @param list<int|float> $values
     */
    public function __construct(array $values)
    {
        $this->values = Sort::list(\array_values($values));
        $this->count = \count($this->values) ?: throw new \UnderflowException(
            'Cannot calculate percentiles from a empty list.',
        );
    }

    public function percentile(int|float $score): int|float
    {
        if ($score < 0.0 || $score > 1.0) {
            throw new \UnexpectedValueException($score . ' is not a valid percentile. Must be between 0 and 1.');
        }

        // Calculate the rank/position of the percentile for a zero-based index.
        // For example, in a list of 10 items (indices 0-9), the 50th percentile
        // would be at position (10-1) * 0.5 = 4.5.
        $position = ($this->count - 1) * $score;

        // Find the integer part of the position, giving us the index of the
        // element *before* our calculated position (the lower bound for the percentile).
        $index = (int)\floor($position);

        // Get the lower and upper bounds for the percentile value.
        // For $base = 4, this would be the 5th and 6th elements in the array.
        $lower_bound = $this->values[$index];
        $upper_bound = $this->values[$index + 1] ?? null;

        // If there is no upper bound, we are at the last or only element, and
        // we can return the lower bound as the percentile value.
        if ($upper_bound === null) {
            return $lower_bound;
        }

        // Perform linear interpolation (see https://en.wikipedia.org/wiki/Linear_interpolation)
        // between the lower and upper bounds to calculate the percentile value.
        return $lower_bound + ($position - $index) * ($upper_bound - $lower_bound);
    }
}
