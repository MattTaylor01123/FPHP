<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use src\utilities\IterableGenerator;

final class SkipTest extends TestCase
{
    public function testSkip()
    {
        $this->assertEquals([], F::skip(5, []));
        $this->assertEquals([4,5,6], f::skip(3, [1,2,3,4,5,6]));
        $this->assertEquals([3,4], F::skip(2, ["a" => 1, "b" => 2, "c" => 3, "d" => 4]));
        $this->assertEquals(["c" => 3, "d" => 4], F::skipK(2, ["a" => 1, "b" => 2, "c" => 3, "d" => 4]));
        $this->assertEqualsIter($this->makeIterable([3,4,5]), F::skip(2, $this->makeIterable([1,2,3,4,5])));
        $this->assertEqualsIterK($this->makeIterable(["c" => 3, "d" => 4]), F::skipK(2, $this->makeIterable(["a" => 1, "b" => 2, "c" => 3, "d" => 4])));
    }

    public function testSkipWhile()
    {
        $pred = fn($v) => $v < 5;
        $this->assertEquals([], F::skipWhile($pred, []));
        $this->assertEquals([6,4,7], F::skipWhile($pred, [1,2,6,4,7]));
        $this->assertEquals([6,4], F::skipWhile($pred, ["a" => 1, "b" => 2, "c" => 6, "d" => 4]));
        $this->assertEquals(["c" => 6, "d" => 4], F::skipWhileK($pred, ["a" => 1, "b" => 2, "c" => 6, "d" => 4]));
        $this->assertEqualsIter($this->makeIterable([6, 4]), F::skipWhile($pred, $this->makeIterable(["a" => 1, "b" => 2, "c" => 6, "d" => 4])));
        $this->assertEqualsIterK($this->makeIterable(["c" => 6, "d" => 4]), F::skipWhileK($pred, $this->makeIterable(["a" => 1, "b" => 2, "c" => 6, "d" => 4])));
    }

    private function makeIterable(array $vals)
    {
        $x = new IterableGenerator(fn() => yield from $vals);
        return $x;
    }

    private function assertEqualsIter($exp, $act)
    {
        $expArr = iterator_to_array($exp, false);
        $actArr = iterator_to_array($act, false);
        $this->assertEquals($expArr, $actArr);
    }

    private function assertEqualsIterK($exp, $act)
    {
        $expArr = iterator_to_array($exp, true);
        $actArr = iterator_to_array($act, true);
        $this->assertEquals($expArr, $actArr);
    }
}