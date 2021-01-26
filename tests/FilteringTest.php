<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;
use Traversable;

final class FilteringTest extends TestCase
{
    use TestUtils;

    function testFilterIdx()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = R::filter($fnEven, $this->getIndexedArray());
        $this->assertSame($res, [2,4]);
    }

    function testFilterAssoc()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $res2 = R::filter($fnEven, $this->getAssocArray());
        $this->assertSame($res2, ["b" => 2, "d" => 4]);
    }

    function testFilterObj()
    {
        $fnEven = fn ($v) => $v % 2 === 0;
        $obj = (object)$this->getAssocArray();
        $res3 = R::filter($fnEven, $obj);
        $this->assertEquals($res3, (object)["b" => 2, "d" => 4]);
    }

    function testFilterItIdx()
    {
        $fnEven = fn ($v) => $v > 25;
        $res = R::filter($fnEven, $this->getItIdx());
        $this->assertTrue(is_object($res));
        $this->assertTrue($res instanceof Traversable);
        $this->assertSame(iterator_to_array($res, false), [30, 40]);
    }

    function testFilterItAssoc()
    {
        $fnEven = fn ($v) => $v > 25;
        $res = R::filter($fnEven, $this->getItAssoc());
        $this->assertTrue(is_object($res));
        $this->assertTrue($res instanceof Traversable);
        $this->assertSame(iterator_to_array($res, true), ["k" => 30, "l" => 40]);
    }

    function testFilterOverride()
    {
        $fnEven = fn ($v) => $v > 25;
        $collection = $this->buildCollectionMock("filter", $fnEven, ["hello", "world"]);
        $out2 = R::filter($fnEven, $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testReject()
    {
        $arr = [1,2,3,4,5,6];
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = R::reject($fnEven, $arr);

        $this->assertSame($res, [1,3,5]);
    }
}