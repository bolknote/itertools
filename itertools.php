<?php #5.6

namespace itertools;

function slice($start_or_stop, $stop = PHP_INT_MAX, $step = 1)
{
    if (func_num_args() === 1) {
        return (object) [
            'start' => 0,
            'stop'  => $start_or_stop,
            'step'  => $step,
        ];
    }

    return (object) [
        'start' => $start_or_stop,
        'stop'  => $stop === null ? PHP_INT_MAX : $stop,
        'step'  => $step,
    ];
}

function enumerate($iterable, $start = 0)
{
    $n = $start;

    foreach (iter($iterable) as $value) {
        yield [$n, $value];
        $n++;
    }
}

function iter($var)
{
    switch (true) {
        case $var instanceof \Iterator:
            return $var;

        case $var instanceof \Traversable:
        	return new \IteratorIterator($var);

        case is_string($var):
            $var = str_split($var);
            /* проваливаемся */

        case is_array($var):
            return new \ArrayIterator($var);

        default:
            $type = gettype($var);
            throw new \InvalidArgumentException("'$type' type is not iterable");
    }
}

function xrange($start_or_stop, $stop = PHP_INT_MAX, $step = 1)
{
    $args = slice(...func_get_args());

    if ($args->step == 0) {
        throw new \InvalidArgumentException('xrange() arg 3 must not be zero');
    }

    if ($args->start > $args->stop && $args->step > 0 || $args->start < $args->stop && $args->step < 0) {
        return;
    }

    for ($i = $args->start; $i != $args->stop; $i += $args->step) {
        yield $i;
    }
}

function chain(...$iterables)
{
    foreach ($iterables as $it) {
        foreach (iter($it) as $element) {
            yield $element;
        }
    }
}

function from_iterable($iterables)
{
    foreach (iter($iterables) as $it) {
        foreach (iter($it) as $element) {
            yield $element;
        }
    }
}

function combinations($iterable, $r)
{
	$pool = is_array($iterable) ? $iterable : iterator_to_array(iter($iterable));
	$n = sizeof($pool);

	if ($r > $n) {
		return;
	}

	$indices = range(0, $r-1);
	yield array_slice($pool, 0, $r);

	for (;;) {
		for (;;) {
			for ($i = $r - 1; $i >= 0; $i--) {
				if ($indices[$i] != $i + $n - $r) {
					break 2;
				}
			}

			return;
		}

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

function combinations_with_replacement($iterable, $r)
{
	$pool = is_array($iterable) ? $iterable : iterator_to_array(iter($iterable));
	$n = sizeof($pool);

	if (!$n && $r) {
		return;
	}

	$indices = array_fill(0, $r, 0);
	yield array_slice($pool, 0, $r);

	for (;;) {
		for (;;) {
			for ($i = $r - 1; $i >= 0; $i--) {
				if ($indices[$i] != $n - 1) {
					break 2;
				}
			}

			return;
		}

		array_splice($indices, $i, sizeof($indices), array_fill(0, $r - $i, $indices[$i] + 1));

		$row = [];
		foreach ($indices as $i) {
			$row[] = $pool[$i];
		}

		yield $row;
	}
}

function compress($data, $selectors)
{
    foreach (izip($data, $selectors) as list($d, $s)) {
        if ($s) {
            yield $d;
        }
    }
}

function count($start = 0, $step = 1)
{
    for ($i = $start;; $i += $step) {
        yield $i;
    }
}

function cycle($iterable)
{
    return new \InfiniteIterator(iter($iterable));
}

function dropwhile(callable $predicate, $iterable)
{
    $iterable = iter($iterable);

    foreach ($iterable as $x) {
        if (!$predicate($x)) {
            yield $x;
            break;
        }
    }

    foreach ($iterable as $x) {
        yield $x;
    }
}

function groupby($it, callable $keyfunc = null)
{
    $it = iter($it);
    $tgtkey = $currkey = $currvalue = (object) [];

    $grouper = function ($tgtkey) use (&$currkey, &$currvalue, $keyfunc, $it) {
        while ($currkey == $tgtkey) {
            yield $currvalue;

            if (!$it->valid()) {
                return;
            }

            $currvalue = $it->current();
            $it->next();

            $currkey = $keyfunc === null ? $currvalue : $keyfunc($currvalue);
        }
    };

    for (;;) {
        while ($currkey === $tgtkey) {
            if (!$it->valid()) {
                return;
            }

            $currvalue = $it->current();
            $it->next();

            $currkey = $keyfunc === null ? $currvalue : $keyfunc($currvalue);
        }

        $tgtkey = $currkey;

        yield [$currkey, $grouper($tgtkey)];
    }
}

function ifilter(callable $predicate = null, $iterable)
{
    if ($predicate === null) {
        $predicate = 'boolval';
    }

    foreach (iter($iterable) as $x) {
        if ($predicate($x)) {
            yield $x;
        }
    }
}

function ifilterfalse(callable $predicate = null, $iterable)
{
    if ($predicate === null) {
        $predicate = 'boolval';
    }

    foreach (iter($iterable) as $x) {
        if (!$predicate($x)) {
            yield $x;
        }
    }
}

function imap(callable $function = null, ...$iterables)
{
	foreach (izip(...$iterables) as $args) {
		if ($function === null) {
			yield $args;
		} else {
			yield $function(...$args);
		}
	}
}

function islice($iterable, ...$args)
{
    if (slice(...$args)->step < 1) {
        throw new \InvalidArgumentException('Step for islice() must be a positive integer or null.');
    }

    $it = xrange(...$args);
    if ($it->valid()) {
        $nexti = $it->current();

        foreach (enumerate($iterable) as list($i, $element)) {
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

function izip(...$iterables)
{
	$multipleIterator = new \MultipleIterator();
	foreach ($iterables as $iterable) {
		$multipleIterator->attachIterator(iter($iterable));
	}

	return $multipleIterator;
}

function izip_longest(/* ...$iterables[, $fillvalue = null] */ ...$args)
{
    $fillvalue = array_pop($args);
	$counter   = sizeof($args);
    $iterables = array_map('iter', $args);

	$sentinel = function() use (&$counter, $fillvalue) {
		$counter--;
		yield $fillvalue;
	};

	$fillers = repeat($fillvalue);

	$iterators = array_map(function ($it) use ($sentinel, $fillers) {
		return chain($it, $sentinel(), $fillers);
	}, $iterables);


	for (;;) {
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

function permutations($iterable, $r = null)
{
	$pool = is_array($iterable) ? $iterable : iterator_to_array(iter($iterable));
	$n = sizeof($pool);
	$r = $r === null ? $n : $r;

	if ($r > $n) {
		return;
	}

	$indices = range(0, $n - 1);
	$cycles  = range($n, $n - $r - 1, -1);

	yield array_slice($pool, 0, $r);

	while ($n) {
		for (;;) {
			for ($i = $r - 1; $i >= 0; $i--) {
				$cycles[$i]--;
				$exit = false;

				if ($cycles[$i] === 0) {
					$indices[] = array_splice($indices, $i, 1)[0];
					$cycles[$i] = $n - $i;
				} else {
					$j = $cycles[$i];
					$minus_j = sizeof($indices) - $j;

					list($indices[$i], $indices[$minus_j]) = [$indices[$minus_j], $indices[$i]];

					$row = [];
					for ($i = 0; $i<$r; $i++) {
						$row[] = $pool[$indices[$i]];
					}

					yield $row;
					break 2;
				}
			}

			return;
		}
	}
}

function product(/*...$iterables[, $repeat = 1]*/ ...$args)
{
    $repeat = array_pop($args);
    $iterables = array_map('iter', $args);

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

    foreach ($result as $prod) {
        yield $prod;
    }
}

function repeat($object, $times = null)
{
    if ($times === null) {
        for (;;) {
            yield $object;
        }
    } else {
        for ($i = 0; $i < $times; $i++) {
            yield $object;
        }
    }
}

function starmap(callable $function, $iterable)
{
    foreach (iter($iterable) as $args) {
        yield $function(...$args);
    }
}

function takewhile(callable $predicate, $iterable)
{
    foreach (iter($iterable) as $x) {
        if ($predicate($x)) {
            yield $x;
        } else {
            break;
        }
    }
}

function tee($iterable, $n = 2)
{
	$it = new \CachingIterator(iter($iterable), \CachingIterator::FULL_CACHE);
	$result = [$it];

	for ($i = 1; $i<$n; $i++) {
		$result[] = call_user_func(function() use ($it) {
			foreach ($it->getCache() as $key => $value) {
				yield $key => $value;
			}		
		});
	}

	return $result;
}
