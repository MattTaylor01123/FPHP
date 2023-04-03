<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use stdClass;
use tests\TestType;
use Traversable;

final class AdjustTest extends TestCase
{
    public function testAdjustArrayIdx()
    {
        $v1 = [1,2,3,4,5];
        $o1 = F::adjust(2, fn($v) => $v+1, $v1);
        $this->assertEquals([1,2,3,4,5], $v1);
        $this->assertEquals([1,2,4,4,5], $o1);
    }

    public function testAdjustArrayAssoc()
    {
        $v2 = [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ];
        $o2 = F::adjust("c", fn($v) => $v-1, $v2);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], $v2);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 2, "d" => 4, "e" => 5], $o2);
    }

    public function testAdjustItIndexed()
    {
        $count = 0;
        $fn = function($x) use(&$count) {
            $count = $count + 1;
            return $x + 1;
        };

        $v4 = new class() implements IteratorAggregate
        {
            public function getIterator(): Traversable
            {
                yield 10;
                yield 20;
                yield 30;
                yield 40;
            }
        };
        $o4 = F::adjust(2, $fn, $v4);

        $this->assertTrue($o4 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($o4 as $k => $v)
        {
            $results[$k] = $v;
            $this->assertEquals(in_array($k, [2, 3]) ? 1 : 0, $count);
        }

        // check overall result
        $this->assertEquals([10, 20, 31, 40], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals([10, 20, 31, 40], iterator_to_array($o4));
        $this->assertEquals(2, $count);
    }
    
    public function testAdjustItAssoc()
    {
        $count = 0;
        $fn = function($x) use(&$count) {
            $count = $count + 1;
            return $x + 1;
        };

        $v4 = new class() implements IteratorAggregate
        {
            public function getIterator(): Traversable
            {
                yield "i" => 10;
                yield "j" => 20;
                yield "k" => 30;
                yield "l" => 40;
            }
        };
        $o4 = F::adjust("k", $fn, $v4);

        $this->assertTrue($o4 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($o4 as $k => $v)
        {
            $results[$k] = $v;
            $this->assertEquals(in_array($k, ["k", "l"]) ? 1 : 0, $count);
        }

        // check overall result
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 31, "l" => 40], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 31, "l" => 40], iterator_to_array($o4));
        $this->assertEquals(2, $count);
    }
    
    public function testThreading()
    {
        $v1 = [1,2,3,4,5];
        $fn = F::adjust(2, fn($v) => $v+1);
        $this->assertTrue(is_callable($fn));
        $o1 = $fn($v1);
        $this->assertEquals([1,2,3,4,5], $v1);
        $this->assertEquals([1,2,4,4,5], $o1);       
    }
}