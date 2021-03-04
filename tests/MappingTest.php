<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;
use Traversable;

final class MappingTest extends TestCase
{
    use TestUtils;

    // indexBy -----------------------------------------------------------------

    function testIndexByIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::indexBy(F::prop("gender"), $v);
        $this->assertIsArray($out1);
        $this->assertCount(2, $out1);
        $this->assertArrayHasKey("M", $out1);
        $this->assertArrayHasKey("F", $out1);
        $this->assertSame($v[2], $out1["M"]);
        $this->assertSame($v[4], $out1["F"]);
    }

    function testIndexByItAssoc()
    {
        $v = $this->getPersonsDataIt();
        $out1 = F::indexBy(F::prop("gender"), $v);
        $this->assertTrue(is_object($out1));
        $this->assertTrue($out1 instanceof Traversable);

        $i = 0;
        $v2 = $this->getPersonsDataIdx();
        foreach($out1 as $k => $val)
        {
            $this->assertEquals($v2[$i], $val);
            $this->assertSame($v2[$i]->gender, $k);
            $i++;
        }
    }

    function testIndexByOverride()
    {
        $fn = F::prop("family");
        $collection = $this->buildCollectionMock("indexBy", $fn, ["hello", "world"]);
        $out2 = F::indexBy($fn, $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testIndexByTransducer()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::transduce(F::indexBy(F::prop("gender")), F::assoc(), [], $v);
        $this->assertIsArray($out1);
        $this->assertCount(2, $out1);
        $this->assertArrayHasKey("M", $out1);
        $this->assertArrayHasKey("F", $out1);
        $this->assertSame($v[2], $out1["M"]);
        $this->assertSame($v[4], $out1["F"]);
    }

    // map ---------------------------------------------------------------------

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
        $fn = fn ($x) => $x * 2;
        $o1 = F::map($fn, $this->getItAssoc());
        $this->assertTrue(is_object($o1));
        $this->assertTrue($o1 instanceof Traversable);
        $this->assertEquals(iterator_to_array($o1), ["i" => 20, "j" => 40, "k" => 60, "l" => 80]);
        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(iterator_to_array($o1), ["i" => 20, "j" => 40, "k" => 60, "l" => 80]);
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

    // pluck -------------------------------------------------------------------

    function testPluckIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::pluck("name", $v);
        $this->assertSame($out1, ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testPluckIt()
    {
        $v = $this->getPersonsDataIt();
        $out1 = F::pluck("name", $v);
        $this->assertTrue(is_object($out1));
        $this->assertTrue($out1 instanceof Traversable);
        $this->assertSame(iterator_to_array($out1, false), ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testPluckOverride()
    {
        $collection = $this->buildCollectionMock("pluck", "name", ["hello", "world"]);
        $out2 = F::pluck("name", $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }
}