<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use src\utilities\IterableGenerator;
use Traversable;

final class FilterTest extends TestCase
{
    function testFilterIdx()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = F::filter($fnEven, [1,2,3,4,5]);
        $this->assertSame($res, [2,4]);
        $resK = F::filterK($fnEven, [1,2,3,4,5]);
        $this->assertSame($resK, ["1" => 2, "3" => 4]);
    }

    function testFilterAssoc()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = F::filter($fnEven, [
            "a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $this->assertSame($res, [2, 4]);
        $resK = F::filterK($fnEven, [
            "a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $this->assertSame($resK, ["b" => 2, "d" => 4]);
    }

    function testFilterObj()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $obj = (object)[
            "a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $res = F::filter($fnEven, $obj);
        $this->assertEquals($res, [2, 4]);
        $resK = F::filterK($fnEven, $obj);
        $this->assertEquals($resK, (object)["b" => 2, "d" => 4]);
    }

    function testFilterItIdx()
    {
        $count = 0;
        $fnEven = function($v) use(&$count) {
            $count = $count + 1;
            return $v > 25;
        };
        $res = F::filter($fnEven, new IterableGenerator(fn() => yield from [
            10, 20, 30, 40]));
        
        $this->assertTrue($res instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($res as $v)
        {
            $results[] = $v;
            $this->assertEquals(count($results)+2, $count);
        }

        // check overall result
        $this->assertSame([30, 40], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertSame([30, 40], iterator_to_array($res, false));
        $this->assertEquals(8, $count);
    }

    function testFilterItAssoc()
    {
        $count = 0;
        $fnEven = function($v) use(&$count) {
            $count = $count + 1;
            return $v > 25;
        };
        $res = F::filterK($fnEven, new IterableGenerator(fn() => yield from [
            "i" => 10, "j" => 20, "k" => 30, "l" => 40]));

        $this->assertTrue($res instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($res as $k => $v)
        {
            $results[$k] = $v;
            $this->assertEquals(count($results)+2, $count);
        }

        // check overall result
        $this->assertSame(["k" => 30, "l" => 40], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertSame(["k" => 30, "l" => 40], iterator_to_array($res, true));
        $this->assertEquals(8, $count);
    }
    
    public function testFilterThread()
    {
        $fn = F::filter(fn($v) => $v % 2 === 0);
        $this->assertTrue(is_callable($fn));
        $out = $fn([1,2,3,4,5]);
        $this->assertEquals([2,4], $out);
        
        $fn2 = F::filterK(fn($v, $k) => $k === "m");
        $this->assertTrue(is_callable($fn2));
        $out2 = $fn2(["l" => 1, "m" => 2, "n" => 3]);
        $this->assertEquals(["m" => 2], $out2);
    }
    
    function testEarlyCompletion()
    {
        $transducer = F::compose(
            F::filterT(fn($v) => $v !== "d"),
            F::partitionByT(fn($v, $k) => intval($k / 3)),
            F::mapT(fn($v) => implode("", $v))
        );
        
        $input = ["a", "b", "c", "d", "e", "f", "g", "h"];
        $out = F::transduce($transducer, fn($acc, $v) => F::append($acc, $v), [], $input);
        $this->assertSame(["abc", "efg", "h"], $out);
    }
}