<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;
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
        $fn = fn ($v, $k) => $k.$v;
        $o1 = F::map($fn, $this->getAssocArray());
        $this->assertSame(["a" => "a1", "b" => "b2", "c" => "c3", "d" => "d4", "e" => "e5"], $o1);
    }

    function testMapObj()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = F::map($fn, $this->getObj());
        $this->assertEquals((object)["f" => 4, "g" => 8, "h" => 12], (object)$o1);
    }

    function testMapItIdx()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = F::map($fn, $this->getItIdx());
        $this->assertTrue(is_object($o1));
        $this->assertTrue($o1 instanceof Traversable);
        $this->assertEquals(iterator_to_array($o1), [20, 40, 60, 80]);
        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(iterator_to_array($o1), [20, 40, 60, 80]);
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

        $this->assertEquals(0, $count);
        $this->assertEquals(iterator_to_array($o1), ["i" => 20, "j" => 40, "k" => 60, "l" => 80]);
        $this->assertEquals(4, $count);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(iterator_to_array($o1), ["i" => 20, "j" => 40, "k" => 60, "l" => 80]);
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
}