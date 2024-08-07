<?php
declare(strict_types=1);

namespace Itertools;

use ArrayIterator;
use CachingIterator;
use Generator;
use InfiniteIterator;
use InvalidArgumentException;
use Iterator;
use IteratorIterator;
use MultipleIterator;
use Traversable;

class Itertools
{
    private static function slice(int $start_or_stop, int $stop = PHP_INT_MAX, int $step = 1): object
    {
        if (func_num_args() === 1) {
            return (object)[
                'start' => 0,
                'stop' => $start_or_stop,
                'step' => $step,
            ];
        }

        return (object)[
            'start' => $start_or_stop,
            'stop' => $stop ?? PHP_INT_MAX,
            'step' => $step,
        ];
    }


    private static function iter(string|iterable $var): iterable|IteratorIterator|Iterator|ArrayIterator
    {
        switch (true) {
            case $var instanceof Iterator:
                return $var;

            case $var instanceof Traversable:
                return new IteratorIterator($var);

            /** @noinspection PhpMissingBreakStatementInspection */
            case is_string($var):
                $var = str_split($var);
            /* fall-through */

            case is_array($var):
                return new ArrayIterator($var);

            default:
                $type = gettype($var);
                throw new InvalidArgumentException("'$type' type is not iterable");
        }
    }

    public static function enumerate(string|iterable $iterable, int $start = 0): Generator
    {
        $n = $start;

        foreach (static::iter($iterable) as $value) {
            yield [$n, $value];
            $n++;
        }
    }

    public static function xrange(int $start_or_stop, int $stop = PHP_INT_MAX, int $step = 1): Generator
    {
        $args = static::slice(...func_get_args());

        if ($args->step === 0) {
            throw new InvalidArgumentException('xrange() arg 3 must not be zero');
        }

        if (($args->start > $args->stop && $args->step > 0) || ($args->start < $args->stop && $args->step < 0)) {
            return;
        }

        for ($i = $args->start; $i !== $args->stop; $i += $args->step) {
            yield $i;
        }
    }

    public static function chain(string|iterable ...$iterables): Generator
    {
        foreach ($iterables as $it) {
            foreach (static::iter($it) as $element) {
                yield $element;
            }
        }
    }

    public static function from_iterable(string|iterable $iterables): Generator
    {
        foreach (static::iter($iterables) as $it) {
            foreach (static::iter($it) as $element) {
                yield $element;
            }
        }
    }

    public static function combinations(string|iterable $iterable, int $r): Generator
    {
        $pool = is_array($iterable) ? $iterable : iterator_to_array(static::iter($iterable));
        $n = count($pool);

        if ($r > $n) {
            return;
        }

        $indices = range(0, $r - 1);
        yield array_slice($pool, 0, $r);

        for (; ;) {
            for ($i = $r - 1; $i >= 0; $i--) {
                if ($indices[$i] !== $i + $n - $r) {
                    goto next;
                }
            }

            return;

            next:
            $indices[$i]++;

            for ($j = $i + 1; $j < $r; $j++) {
                $indices[$j] = $indices[$j - 1] + 1;
            }

            $row = [];
            foreach ($indices as $i) {
                $row[] = $pool[$i];
            }

            yield $row;
        }
    }

    public static function combinations_with_replacement(string|iterable $iterable, int $r): Generator
    {
        $pool = is_array($iterable) ? $iterable : iterator_to_array(static::iter($iterable));
        if (!$pool && $r > 0) {
            return;
        }

        $n = count($pool);
        yield array_fill(0, $r, $pool[0]);
        $indices = array_fill(0, $r, 0);

        for (; ;) {
            for ($i = $r - 1; $i >= 0; $i--) {
                if ($indices[$i] !== $n - 1) {
                    goto next;
                }
            }

            return;

            next:
            array_splice($indices, $i, count($indices), array_fill(0, $r - $i, $indices[$i] + 1));

            $row = [];
            foreach ($indices as $i) {
                $row[] = $pool[$i];
            }

            yield $row;
        }
    }

    public static function compress(string|iterable $data, string|iterable $selectors): Generator
    {
        foreach (static::izip($data, $selectors) as [$d, $s]) {
            if ($s) {
                yield $d;
            }
        }
    }

    public static function count(int $start = 0, int $step = 1): Generator
    {
        for ($i = $start; ; $i += $step) {
            yield $i;
        }
    }

    public static function cycle(iterable $iterable): InfiniteIterator
    {
        return new InfiniteIterator(static::iter($iterable));
    }

    public static function dropwhile(callable $predicate, string|iterable $iterable): Generator
    {
        $found = false;

        foreach (static::iter($iterable) as $x) {
            if (!$found && !$predicate($x)) {
                $found = true;
            }

            if ($found) {
                yield $x;
            }
        }
    }

    public static function groupby(string|iterable $iterable, callable $keyfunc = null): Generator
    {
        $keyfunc ??= static fn($x) => $x;
        $iterator = static::iter($iterable);
        $exhausted = false;

        while (!$exhausted) {
            if ($iterator->valid()) {
                $curr_value = $iterator->current();
                $curr_key = $keyfunc($curr_value);
            } else {
                $exhausted = true;
                continue;
            }

            $target_key = $curr_key;
            $group = [];

            while ($iterator->valid()) {
                $curr_value = $iterator->current();
                $curr_key = $keyfunc($curr_value);

                if ($curr_key !== $target_key) {
                    break;
                }

                $group[] = $curr_value;
                $iterator->next();
            }

            yield [$target_key, $group];

            if (!$iterator->valid()) {
                $exhausted = true;
            }
        }
    }

    public static function ifilter(?callable $predicate, string|iterable $iterable): Generator
    {
        if ($predicate === null) {
            $predicate = 'boolval';
        }

        foreach (static::iter($iterable) as $x) {
            if ($predicate($x)) {
                yield $x;
            }
        }
    }

    public static function filterfalse(?callable $predicate, string|iterable $iterable): Generator
    {
        if ($predicate === null) {
            $predicate = 'boolval';
        }

        foreach (static::iter($iterable) as $x) {
            if (!$predicate($x)) {
                yield $x;
            }
        }
    }

    public static function imap(callable $function = null, string|iterable ...$iterables): Generator
    {
        foreach (static::izip(...$iterables) as $args) {
            if ($function === null) {
                yield $args;
            } else {
                yield $function(...$args);
            }
        }
    }

    public static function islice(string|iterable $iterable, int ...$args): Generator
    {
        if (static::slice(...$args)->step < 1) {
            throw new InvalidArgumentException('Step for islice() must be a positive integer or null.');
        }

        $it = static::xrange(...$args);
        if ($it->valid()) {
            $nexti = $it->current();

            foreach (static::enumerate($iterable) as [$i, $element]) {
                if ($i === $nexti) {
                    yield $element;

                    $it->next();
                    if (!$it->valid()) {
                        break;
                    }

                    $nexti = $it->current();
                }
            }
        }
    }

    public static function izip(string|iterable ...$iterables): Generator
    {
        $multipleIterator = new MultipleIterator();
        foreach ($iterables as $iterable) {
            $multipleIterator->attachIterator(static::iter($iterable));
        }

        foreach ($multipleIterator as $item) {
            yield $item;
        }
    }

    public static function izip_longest(/* ...$iterables[, $fillvalue] */ string|iterable ...$args): Generator
    {
        $fillvalue = array_pop($args);
        $counter = count($args);
        $iterables = array_map(static::iter(...), $args);

        $sentinel = static function () use (&$counter, $fillvalue) {
            $counter--;
            yield $fillvalue;
        };

        $fillers = static::repeat($fillvalue);

        $iterators = array_map(static fn($it) => static::chain($it, $sentinel(), $fillers), $iterables);

        for (; ;) {
            $row = [];
            foreach ($iterators as $iterator) {
                if (!$iterator->valid()) {
                    return;
                }

                $row[] = $iterator->current();
                $iterator->next();
            }

            yield $row;

            if (!$counter) {
                return;
            }
        }
    }

    public static function permutations(string|iterable $iterable, int $r = null): Generator
    {
        $pool = is_array($iterable) ? $iterable : iterator_to_array(static::iter($iterable));
        $n = count($pool);
        $r ??= $n;

        if ($r > $n) {
            return;
        }

        $indices = range(0, $n - 1);
        $cycles = range($n, $n - $r - 1, -1);

        yield array_slice($pool, 0, $r);

        while ($n) {
            for ($i = $r - 1; $i >= 0; $i--) {
                $cycles[$i]--;

                if ($cycles[$i] === 0) {
                    $indices[] = array_splice($indices, $i, 1)[0];
                    $cycles[$i] = $n - $i;
                } else {
                    $j = $cycles[$i];
                    $minus_j = \count($indices) - $j;

                    [$indices[$i], $indices[$minus_j]] = [$indices[$minus_j], $indices[$i]];

                    $row = [];
                    for ($j = 0; $j < $r; $j++) {
                        $row[] = $pool[$indices[$j]];
                    }

                    yield $row;
                    goto next;
                }
            }

            return;
            next:
        }
    }

    public static function product(/*...$iterables[, $repeat]*/ string|iterable ...$args): Generator
    {
        $repeat = is_int($args[array_key_last($args)]) ? array_pop($args) : 1;
        $iterables = array_map(static::iter(...), $args);

        $pools = array_merge(...array_fill(0, $repeat, $iterables));
        $result = [[]];

        foreach ($pools as $pool) {
            $result_inner = [];

            foreach ($result as $x) {
                foreach ($pool as $y) {
                    $result_inner[] = array_merge($x, [$y]);
                }
            }

            $result = $result_inner;
        }

        yield from $result;
    }

    public static function repeat(mixed $object, int $times = null): Generator
    {
        if ($times === null) {
            for (; ;) {
                yield $object;
            }
        } else {
            for ($i = 0; $i < $times; $i++) {
                yield $object;
            }
        }
    }

    public static function starmap(callable $function, $iterable): Generator
    {
        foreach (static::iter($iterable) as $args) {
            yield $function(...$args);
        }
    }

    public static function takewhile(callable $predicate, $iterable): Generator
    {
        foreach (static::iter($iterable) as $x) {
            if ($predicate($x)) {
                yield $x;
            } else {
                break;
            }
        }
    }

    public static function tee(string|iterable $iterable, int $n = 2): array
    {
        $it = new CachingIterator(static::iter($iterable), CachingIterator::FULL_CACHE);
        $result = [$it];

        for ($i = 1; $i < $n; $i++) {
            $result[] = (static function () use ($it) {
                foreach ($it->getCache() as $key => $value) {
                    yield $key => $value;
                }
            })();
        }

        return $result;
    }

    public static function accumulate(string|iterable $iterable, callable $function = null, int $initial = null): Generator
    {
        $function ??= static fn($a, $b) => $a + $b;
        $iterator = self::iter($iterable);
        $total = $initial;

        if ($initial === null) {
            if ($iterator->valid()) {
                $total = $iterator->current();
                $iterator->next();
            } else {
                return;
            }
        }

        yield $total;

        while ($iterator->valid()) {
            $total = $function($total, $iterator->current());
            $iterator->next();
            yield $total;
        }
    }

    public static function batched(string|iterable $iterable, int $n): Generator
    {
        if ($n < 1) {
            throw new InvalidArgumentException("n must be at least one");
        }

        $iterator = self::iter($iterable);
        while ($iterator->valid()) {
            $batch = [];
            for ($i = 0; $i < $n && $iterator->valid(); $i++) {
                $batch[] = $iterator->current();
                $iterator->next();
            }

            if ($batch) {
                yield $batch;
            }
        }
    }
}
