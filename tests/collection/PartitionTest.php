<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class PartitionTest extends TestCase
{
    public function testPartitionByEmptyArray()
    {
        $out = F::partitionBy(fn($v) => intval($v / 3), []);
        $this->assertTrue(is_array($out));
        $this->assertEquals([], $out);
    }

    public function testPartitionByArray()
    {
        $out = F::partitionBy(fn($v, $k) => intval($v / 3), [1,2,3,4,5,6,7,8,9,10]);
        $this->assertTrue(is_array($out));
        $this->assertEquals([[1,2],[3,4,5],[6,7,8],[9,10]], $out);
    }

    public function testPartitionByEmptyIterable()
    {
        $out = F::partitionBy(fn($v) => intval($v / 3), $this->itGen([]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([], iterator_to_array($out, true));
    }

    public function testPartitionByIterable()
    {
        $out = F::partitionBy(fn($v, $k) => intval($v / 3), $this->itGen([1,2,3,4,5,6,7,8,9,10]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([[1,2],[3,4,5],[6,7,8],[9,10]], iterator_to_array($out, true));
    }

    public function testPartitionByLazy()
    {
        $x = 0;

        $in = $this->itGen([0,1,2,3,4,5,6,7,8,9]);

        $tapped = F::tap(function() use(&$x) {
            $x++;
        }, $in);
        $parted = F::partitionBy(fn($v, $k) => intval($v / 2), $tapped);
        $out = F::take(2, $parted);

        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([[0,1],[2,3]], iterator_to_array($out, true));
        $this->assertEquals(5, $x);
    }

    // MAP

    public function testPartitionMapByEmptyArray()
    {
        $out = F::partitionMapBy(fn($v) => intval($v / 3), fn($v) => $v * 10, []);
        $this->assertTrue(is_array($out));
        $this->assertEquals([], $out);
    }

    public function testPartitionMapByArray()
    {
        $out = F::partitionMapBy(fn($v, $k) => intval($k / 3), fn($v, $k) => $v * 100, [1,2,3,4,5,6,7,8,9,10]);
        $this->assertTrue(is_array($out));
        $this->assertEquals([[100,200,300],[400,500,600],[700,800,900],[1000]], $out);
    }

    public function testPartitionMapByEmptyIterable()
    {
        $out = F::partitionMapBy(fn($v) => intval($v / 3), fn($v) => $v * 10, $this->itGen([]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([], iterator_to_array($out, true));
    }

    public function testPartitionMapByIterable()
    {
        $out = F::partitionMapBy(fn($v, $k) => intval($k / 3), fn($v, $k) => $v * 100, $this->itGen([1,2,3,4,5,6,7,8,9,10]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([[100,200,300],[400,500,600],[700,800,900],[1000]], iterator_to_array($out, true));
    }

    public function testPartitionMapByLazy()
    {
        $x = 0;

        $in = $this->itGen([0,1,2,3,4,5,6,7,8,9]);

        $tapped = F::tap(function() use(&$x) {
            $x++;
        }, $in);
        $parted = F::partitionMapBy(fn($v, $k) => intval($v / 2), fn($v) => $v * 2, $tapped);
        $out = F::take(2, $parted);

        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([[0,2],[4,6]], iterator_to_array($out, true));
        $this->assertEquals(5, $x);
    }

    // REDUCE

    public function testPartitionReduceByEmptyArray()
    {
        $out = F::partitionReduceBy(fn($v) => intval($v / 3), fn($a, $v) => $a + $v, 0, []);
        $this->assertTrue(is_array($out));
        $this->assertEquals([], $out);
    }
    
    public function testPartitionReduceByArray()
    {
        $out = F::partitionReduceBy(fn($v, $k) => intval($k / 3), fn($acc, $v) => $acc + $v, 0, [1,2,3,4,5,6,7,8,9,10]);
        $this->assertTrue(is_array($out));
        $this->assertEquals([6,15,24,10], $out);
    }

    public function testPartitionReduceByEmptyIterable()
    {
        $out = F::partitionReduceBy(fn($v) => intval($v / 3), fn($a, $v) => $a + $v, 0, $this->itGen([]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([], iterator_to_array($out, true));
    }

    public function testPartitionReduceByIterable()
    {
        $out = F::partitionReduceBy(fn($v, $k) => intval($k / 3), fn($acc, $v) => $acc + $v, 0, $this->itGen([1,2,3,4,5,6,7,8,9,10]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([6,15,24,10], iterator_to_array($out, true));
    }

    public function testPartitionReduceByLazy()
    {
        $x = 0;

        $in = $this->itGen([0,1,2,3,4,5,6,7,8,9]);

        $tapped = F::tap(function() use(&$x) {
            $x++;
        }, $in);
        $parted = F::partitionReduceBy(fn($v, $k) => intval($v / 2), fn($a, $v) => $a + $v, 0, $tapped);
        $out = F::take(2, $parted);

        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([1,5], iterator_to_array($out, true));
        $this->assertEquals(5, $x);
    }

    // keys seem to be wrong

    private function itGen($arr)
    {
        yield from $arr;
    }
}