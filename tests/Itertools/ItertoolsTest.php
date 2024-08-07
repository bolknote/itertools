<?php
declare(strict_types=1);

namespace Itertools;

use PHPUnit\Framework\TestCase;
use Itertools\Itertools as it;

final class ItertoolsTest extends TestCase
{
    public function testEnumerate(): void
    {
        $iterable = [1, 2, 3];
        $result = iterator_to_array(it::enumerate($iterable));
        $this->assertEquals([[0, 1], [1, 2], [2, 3]], $result);
    }

    public function testRange(): void
    {
        $result = iterator_to_array(it::range(2, 10, 2));
        $this->assertEquals([2, 4, 6, 8], $result);
    }

    public function testChain(): void
    {
        $result = iterator_to_array(it::chain([1, 2], [3, 4]));
        $this->assertEquals([1, 2, 3, 4], $result);
    }

    public function testCombinations(): void
    {
        $result = iterator_to_array(it::combinations([1, 2, 3], 2));
        $this->assertEquals([[1, 2], [1, 3], [2, 3]], $result);
    }

    public function testCombinationsWithReplacement(): void
    {
        $result = iterator_to_array(it::combinations_with_replacement([1, 2], 2));
        $this->assertEquals([[1, 1], [1, 2], [2, 2]], $result);
    }

    public function testCompress(): void
    {
        $result = iterator_to_array(it::compress([1, 2, 3], [1, 0, 1]));
        $this->assertEquals([1, 3], $result);
    }

    public function testIslice(): void
    {
        $result = iterator_to_array(it::islice([1, 2, 3, 4, 5], 1, 4, 2));
        $this->assertEquals([2, 4], $result);
    }

    public function testCount(): void
    {
        $result = it::count(1, 2);
        $this->assertEquals([1, 3, 5, 7, 9], iterator_to_array(it::islice($result, 5)));
    }

    public function testCycle(): void
    {
        $result = it::cycle([1, 2]);
        $this->assertEquals([1, 2, 1, 2, 1], iterator_to_array(it::islice($result, 5)));
    }

    public function testDropwhile(): void
    {
        $result = iterator_to_array(it::dropwhile(fn($x) => $x < 3, [1, 2, 3, 4]));
        $this->assertEquals([3, 4], $result);
    }

    public function testGroupby(): void
    {
        $result = iterator_to_array(it::groupby('AAAABBBCCD'));

        $this->assertEquals([
            ['A', ['A', 'A', 'A', 'A']],
            ['B', ['B', 'B', 'B']],
            ['C', ['C', 'C']],
            ['D', ['D']],
        ], $result);
    }

    public function testIfilter(): void
    {
        $result = iterator_to_array(it::ifilter(fn($x) => $x & 1, [1, 2, 3, 4]));
        $this->assertEquals([1, 3], $result);
    }

    public function testImap(): void
    {
        $result = iterator_to_array(it::imap(fn($x, $y) => $x + $y, [1, 2], [3, 4]));
        $this->assertEquals([4, 6], $result);
    }

    public function testIzip(): void
    {
        $result = iterator_to_array(it::izip([1, 2], [3, 4]));
        $this->assertEquals([[1, 3], [2, 4]], $result);
    }

    public function testIzipLongest(): void
    {
        $result = iterator_to_array(it::izip_longest([1, 2], [3], 'X'));
        $this->assertEquals([[1, 3], [2, 'X']], $result);
    }

    public function testPermutations(): void
    {
        $result = iterator_to_array(it::permutations([1, 2, 3], 2));
        $this->assertEquals([[1, 2], [1, 3], [2, 1], [2, 3], [3, 1], [3, 2]], $result);

        $result = iterator_to_array(it::permutations(range(0, 2)));
        $this->assertEquals([[0, 1, 2], [0, 2, 1], [1, 0, 2], [1, 2, 0], [2, 0, 1], [2, 1, 0]], $result);
    }

    public function testProduct(): void
    {
        $result = iterator_to_array(it::product('ABCD', 'xy'));
        $this->assertEquals([['A', 'x'], ['A', 'y'], ['B', 'x'], ['B', 'y'], ['C', 'x'], ['C', 'y'], ['D', 'x'], ['D', 'y']], $result);
    }

    public function testRepeat(): void
    {
        $result = iterator_to_array(it::repeat('a', 3));
        $this->assertEquals(['a', 'a', 'a'], $result);
    }

    public function testStarmap(): void
    {
        $result = iterator_to_array(it::starmap(fn($x, $y) => $x ** $y, [[2, 5], [3, 2], [10, 3]]));
        $this->assertEquals([32, 9, 1000], $result);
    }

    public function testTakewhile(): void
    {
        $result = iterator_to_array(it::takewhile(fn($x) => $x < 3, [1, 2, 3, 4]));
        $this->assertEquals([1, 2], $result);
    }

    public function testFilterfalse(): void
    {
        $result = iterator_to_array(it::filterfalse(fn($x) => $x < 5, [1, 4, 6, 3, 8]));
        $this->assertEquals([6, 8], $result);
    }

    public function testTee(): void
    {
        [$it1, $it2] = it::tee([1, 2, 3], 2);
        $this->assertEquals([1, 2, 3], iterator_to_array($it1));
        $this->assertEquals([1, 2, 3], iterator_to_array($it2));
    }

    public function testAccumulate(): void
    {
        $result = it::accumulate([1,2,3,4,5]);
        $this->assertEquals([1, 3, 6, 10, 15], iterator_to_array($result));
        $result = it::accumulate([1,2,3,4,5], initial: 100);
        $this->assertEquals([100, 101, 103, 106, 110, 115], iterator_to_array($result));
        $result = it::accumulate([3, 4, 6, 2, 1, 9, 0, 7, 5, 8], max(...));
        $this->assertEquals([3, 4, 6, 6, 6, 9, 9, 9, 9, 9], iterator_to_array($result));
    }

    public function testBatched(): void
    {
        $result = it::batched('ABCDEFG', 3);
        $this->assertEquals([['A','B','C'], ['D','E','F'], ['G']], iterator_to_array($result));
    }

    public function testChain_from_iterable(): void
    {
        $result = it::chain_from_iterable(['ABC', 'DEF']);
        $this->assertEquals(['A', 'B', 'C', 'D', 'E', 'F'], iterator_to_array($result));
        $result = it::chain_from_iterable('ABCDEF');
        $this->assertEquals(['A', 'B', 'C', 'D', 'E', 'F'], iterator_to_array($result));
    }

    public function testPairwise(): void
    {
        $result = it::pairwise('ABCD');
        $this->assertEquals([['A','B'], ['B','C'], ['C','D']], iterator_to_array($result));
    }
}
