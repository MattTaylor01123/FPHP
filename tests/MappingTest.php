<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use RamdaPHP\Core as C;
use PHPUnit\Framework\TestCase;
use Traversable;

final class MappingTest extends TestCase
{
    use IterableDefs;

    function buildCollectionMock2(string $overrideFunction, $in, $out)
    {
        $collection =  $this->getMockBuilder(IteratorAggregate::class)
            ->setMethods(["getIterator", $overrideFunction])
            ->getMock();
        $t = $collection->expects($this->once())
            ->method($overrideFunction);
        if($in !== null)
        {
            $t->with($this->equalTo($in));
        }
        if($out !== null)
        {
            $t->willReturn($out);
        }
        return $collection;
    }

    function testMapIdx()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = C::map($fn, $this->getIndexedArray());
        $this->assertSame([2,4,6,8,10], $o1);
    }

    function testMapAssoc()
    {
        $fn = fn ($v, $k) => $k.$v;
        $o1 = C::map($fn, $this->getAssocArray());
        $this->assertSame(["a" => "a1", "b" => "b2", "c" => "c3", "d" => "d4", "e" => "e5"], $o1);
    }

    function testMapObj()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = C::map($fn, $this->getObj());
        $this->assertEquals((object)["f" => 4, "g" => 8, "h" => 12], (object)$o1);
    }

    function testMapItIdx()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = C::map($fn, $this->getItIdx());
        $this->assertTrue(is_object($o1));
        $this->assertTrue($o1 instanceof Traversable);
        $this->assertEquals(iterator_to_array($o1), [20, 40, 60, 80]);
    }

    function testMapItAssoc()
    {
        $fn = fn ($x) => $x * 2;
        $o1 = C::map($fn, $this->getItAssoc());
        $this->assertTrue(is_object($o1));
        $this->assertTrue($o1 instanceof Traversable);
        $this->assertEquals(iterator_to_array($o1), ["i" => 20, "j" => 40, "k" => 60, "l" => 80]);
    }

    function testMapOverride()
    {
        $fn = fn ($x) => $x * 2;
        $collection = $this->buildCollectionMock2("map", $fn, ["hello", "world"]);
        $o2 = C::map($fn, $collection);
        $this->assertSame($o2, ["hello", "world"]);
    }

    function testIndexByIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = C::indexBy(C::prop("gender"), $v);
        $this->assertIsArray($out1);
        $this->assertCount(2, $out1);
        $this->assertArrayHasKey("M", $out1);
        $this->assertArrayHasKey("F", $out1);
        $this->assertSame($v[2], $out1["M"]);
        $this->assertSame($v[4], $out1["F"]);
    }

    function testIndexByAssoc()
    {
        $v = $this->getPersonsDataIt();
        $out1 = C::indexBy(C::prop("gender"), $v);
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
        $v = $this->getPersonsDataIdx();
        $fn = C::prop("family");
        $collection = $this->buildCollectionMock2("indexBy", $fn, ["hello", "world"]);
        $out2 = C::indexBy($fn, $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    // function testMapTo()
    // {
    //   TODO
    // }

    function testColumnIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = C::column("name", $v);
        $this->assertSame($out1, ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testColumnIt()
    {
        $v = $this->getPersonsDataIt();
        $out1 = C::column("name", $v);
        $this->assertTrue(is_object($out1));
        $this->assertTrue($out1 instanceof Traversable);
        $this->assertSame(iterator_to_array($out1, false), ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testColumnOverride()
    {
        $collection = $this->buildCollectionMock2("column", "name", ["hello", "world"]);
        $out2 = C::column("name", $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testColumnsIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = C::columns(["name", "family"], $v);
        $this->assertSame($out1, [
            ["Matt", "Smith"],
            ["Sheila", "Smith"],
            ["Steve", "Jones"],
            ["Cecilia", "Jones"],
            ["Verity", "Smith"]
        ]);
    }

    function testColumnsOverride()
    {
        $collection = $this->buildCollectionMock2("columns", ["name", "family"], ["hello", "world"]);
        $out2 = C::columns(["name", "family"], $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }
}