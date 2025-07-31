<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Math\Statistics;

final class SummaryStatistics
{
    //phpcs:disable
    public int|float $range {
        get => $this->max - $this->min;
    }
    //phpcs:enable

    public function __construct(
        public readonly int $n,
        public readonly int|float $mean,
        public readonly float $sd,
        public readonly int|float $min,
        public readonly int|float $q1,
        public readonly int|float $median,
        public readonly int|float $q3,
        public readonly int|float $max,
    ) {
    }

    /**
     * @param list<int|float> $values
     */
    public static function population(array $values): self
    {
        return self::calculate($values, true);
    }

    /**
     * @param list<int|float> $values
     */
    public static function sample(array $values): self
    {
        return self::calculate($values, false);
    }

    /**
     * @param list<int|float> $values
     * @param bool $is_population whether the values represent a population or a sample
     */
    private static function calculate(array $values, bool $is_population): self
    {
        $n = \count($values) ?: throw new \UnderflowException('Cannot calculate statistics from an empty list.');

        $distribution = new FiveNumberSummary($values);
        $mean = \array_sum($values) / $n;
        $sum_of_squares = 0.0;
        foreach ($values as $value) {
            $sum_of_squares += ($value - $mean) ** 2;
        }

        return new self(
            n: $n,
            mean: $mean,
            sd: match (true) {
                $n === 1 => 0.0,
                $is_population => \sqrt($sum_of_squares / $n),
                default => \sqrt($sum_of_squares / ($n - 1)),
            },
            min: $distribution->min,
            q1: $distribution->first_quartile,
            median: $distribution->median,
            q3: $distribution->third_quartile,
            max: $distribution->max,
        );
    }
}
