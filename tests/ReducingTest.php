<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\Core as C;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

final class ReducingTest extends TestCase
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

    function testFindIdx()
    {
        $arr = $this->getIndexedArray();
        $v = C::find(fn($a) => $a === 2, $arr);
        $this->assertSame($v, 2);
    }

    function testFindAssoc()
    {
        $arr = $this->getAssocArray();
        $out = C::find(fn($v, $k) => $k === "c", $arr);
        $this->assertSame($out, 3);
    }

    function testFindItIdx()
    {
        $it = $this->getItIdx();
        $out = C::find(fn($v) => $v === 20, $it);
        $this->assertSame($out, 20);
    }

    function testFindItAssoc()
    {
        $it = $this->getItAssoc();
        $out = C::find(fn($v, $k) => $k === "l", $it);
        $this->assertSame($out, 40);
    }

    function testIncludes()
    {
        $arr = [1,2,3,4,5];
        $out1 = C::includes(1, $arr);
        $this->assertSame($out1, true);

        $out2 = C::includes(6, $arr);
        $this->assertSame($out2, false);
    }

    function testIncludesAll()
    {
        $arr = [1,2,3,4,5];
        $out1 = C::includesAll([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = C::includesAll([4,5,6], $arr);
        $this->assertSame($out2, false);
        $out3 = C::includesAll([5,6,7], $arr);
        $this->assertSame($out3, false);
        $out4 = C::includesAll([6,7,8], $arr);
        $this->assertSame($out4, false);
    }

    function testIncludesAny()
    {
        $arr = [1,2,3,4,5];
        $out1 = C::includesAny([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = C::includesAny([4,5,6], $arr);
        $this->assertSame($out2, true);
        $out3 = C::includesAny([5,6,7], $arr);
        $this->assertSame($out3, true);
        $out4 = C::includesAny([6,7,8], $arr);
        $this->assertSame($out4, false);
    }

    function testGroupBy()
    {
        $arr = [
            (object)["id" => 3, "name" => "bob"],
            (object)["id" => 4, "name" => "steve"],
            (object)["id" => 3, "name" => "jackie"],
            (object)["id" => 5, "name" => "paula"]
        ];

        $res = C::groupBy(C::prop("id"), $arr);

        $this->assertEquals($res, [
            3 => [
                (object)["id" => 3, "name" => "bob"],
                (object)["id" => 3, "name" => "jackie"]
            ],
            4 => [
                (object)["id" => 4, "name" => "steve"]
            ],
            5 => [
                (object)["id" => 5, "name" => "paula"]
            ]
        ]);
    }
}