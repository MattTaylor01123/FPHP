<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\RamdaPHP as R;
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
        $v = R::find(fn($a) => $a === 2, $arr);
        $this->assertSame($v, 2);
    }

    function testFindAssoc()
    {
        $arr = $this->getAssocArray();
        $out = R::find(fn($v, $k) => $k === "c", $arr);
        $this->assertSame($out, 3);
    }

    function testFindItIdx()
    {
        $it = $this->getItIdx();
        $out = R::find(fn($v) => $v === 20, $it);
        $this->assertSame($out, 20);
    }

    function testFindItAssoc()
    {
        $it = $this->getItAssoc();
        $out = R::find(fn($v, $k) => $k === "l", $it);
        $this->assertSame($out, 40);
    }

    function testIncludes()
    {
        $arr = [1,2,3,4,5];
        $out1 = R::includes(1, $arr);
        $this->assertSame($out1, true);

        $out2 = R::includes(6, $arr);
        $this->assertSame($out2, false);
    }

    function testIncludesAll()
    {
        $arr = [1,2,3,4,5];
        $out1 = R::includesAll([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = R::includesAll([4,5,6], $arr);
        $this->assertSame($out2, false);
        $out3 = R::includesAll([5,6,7], $arr);
        $this->assertSame($out3, false);
        $out4 = R::includesAll([6,7,8], $arr);
        $this->assertSame($out4, false);
    }

    function testIncludesAny()
    {
        $arr = [1,2,3,4,5];
        $out1 = R::includesAny([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = R::includesAny([4,5,6], $arr);
        $this->assertSame($out2, true);
        $out3 = R::includesAny([5,6,7], $arr);
        $this->assertSame($out3, true);
        $out4 = R::includesAny([6,7,8], $arr);
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

        $res = R::groupBy(R::prop("id"), $arr);

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