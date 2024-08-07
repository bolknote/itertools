<?php
require_once 'src/Itertools/Itertools.php';
use Itertools\Itertools as it;

function ntrim($letter, $n=3, $replby=1): array
{
	$shrink_groups = static function($letter, $n) {
		foreach (it::groupby(it::chain(it::repeat('', $n), $letter, it::repeat('', $n))) as [$item, $grp]) {
			$grp = iterator_to_array($grp);

			yield $item || \count($grp) < $n ? $grp : '';
		}
	};

	return array_slice(iterator_to_array(it::chain(...$shrink_groups($letter, $n))), $replby, -$replby);
}

var_dump(ntrim(['', 1, 2, 3, '', '', '', '', 4, '', '']));
