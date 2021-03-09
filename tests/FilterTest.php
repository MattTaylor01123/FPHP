<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;
use Traversable;

final class FilterTest extends TestCase
{
    use TestUtils;

    function testFilterIdx()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = F::filter($fnEven, $this->getIndexedArray());
        $this->assertSame($res, [2,4]);
    }

    function testFilterAssoc()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res2 = F::filter($fnEven, $this->getAssocArray());
        $this->assertSame($res2, ["b" => 2, "d" => 4]);
    }

    function testFilterObj()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $obj = (object)$this->getAssocArray();
        $res3 = F::filter($fnEven, $obj);
        $this->assertEquals($res3, (object)["b" => 2, "d" => 4]);
    }

    function testFilterItIdx()
    {
        $count = 0;
        $fnEven = function($v) use(&$count) {
            $count = $count + 1;
            return $v > 25;
        };
        $res = F::filter($fnEven, $this->getItIdx());
        
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
        $res = F::filter($fnEven, $this->getItAssoc());

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

    function testFilterOverride()
    {
        $fnEven = fn ($v) => $v > 25;
        $collection = $this->buildCollectionMock("filter", $fnEven, ["hello", "world"]);
        $out2 = F::filter($fnEven, $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }
}