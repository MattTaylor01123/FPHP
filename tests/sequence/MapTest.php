<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;
use Traversable;

final class MapTest extends TestCase
{
    use TestUtils;

    function testMapIdx()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = F::map($fn, $this->getIndexedArray());
        $this->assertEquals([2,4,6,8,10], $o1);
    }

    function testMapAssoc()
    {
        $o1 = F::map(fn ($v, $k) => $k.$v, ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $this->assertSame(["a" => "a1", "b" => "b2", "c" => "c3", "d" => "d4", "e" => "e5"], $o1);
    }

    function testMapItIdx()
    {
        $count = 0;
        $fn = function($x) use(&$count) {
            $count = $count + 1;
            return $x * 2;
        };
        $o1 = F::map($fn, $this->getItIdx());
        
        $this->assertTrue($o1 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($o1 as $v)
        {
            $results[] = $v;
            $this->assertEquals(count($results), $count);
        }

        // check overall result
        $this->assertEquals([20, 40, 60, 80], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals([20, 40, 60, 80], iterator_to_array($o1));
        $this->assertEquals(8, $count);
    }

    function testMapItAssoc()
    {
        $count = 0;
        $fn = function($x) use(&$count) {
            $count = $count + 1;
            return $x * 2;
        };
        $o1 = F::map($fn, $this->getItAssoc());

        $this->assertTrue($o1 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($o1 as $k => $v)
        {
            $results[$k] = $v;
            $this->assertEquals(count($results), $count);
        }

        // check overall result
        $this->assertEquals(["i" => 20, "j" => 40, "k" => 60, "l" => 80], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(["i" => 20, "j" => 40, "k" => 60, "l" => 80], iterator_to_array($o1));
        $this->assertEquals(8, $count);
    }

    function testMapOverride()
    {
        $fn = fn ($x) => $x * 2;
        $collection = $this->buildCollectionMock("map", $fn, ["hello", "world"]);
        $o2 = F::map($fn, $collection);
        $this->assertSame($o2, ["hello", "world"]);
    }

    function testMapInvalid2ndArg()
    {
        $this->expectException(InvalidArgumentException::class);
        F::map(fn($v) => $v * 2, "hello");
    }
    
    function testThreadable()
    {
        $fn = F::map(fn ($v, $k) => $k.$v);
        $this->assertTrue(is_callable($fn));
        $o1 = $fn(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $this->assertSame(["a" => "a1", "b" => "b2", "c" => "c3", "d" => "d4", "e" => "e5"], $o1);       
    }
    
    function testEarlyCompletion()
    {
        $transducer = F::compose(
            F::mapT(fn($v) => strtoupper($v)),
            F::partitionByT(fn($v, $k) => intval($k / 3)),
            F::mapT(fn($v) => implode("", $v))
        );
        
        $input = ["a", "b", "c", "d", "e", "f", "g", "h"];
        $out = F::transduce($transducer, fn($acc, $v) => F::append($acc, $v), [], $input);
        $this->assertSame(["ABC", "DEF", "GH"], $out);
    }
}