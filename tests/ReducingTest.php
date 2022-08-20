<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class ReducingTest extends TestCase
{
    use TestUtils;

    function testFindIdx()
    {
        $arr = $this->getIndexedArray();
        $v = F::find(fn($a) => $a === 2, $arr);
        $this->assertSame($v, 2);
    }

    function testFindAssoc()
    {
        $arr = $this->getAssocArray();
        $out = F::find(fn($v, $k) => $k === "c", $arr);
        $this->assertSame($out, 3);
    }

    function testFindItIdx()
    {
        $it = $this->getItIdx();
        $out = F::find(fn($v) => $v === 20, $it);
        $this->assertSame($out, 20);
    }

    function testFindItAssoc()
    {
        $it = $this->getItAssoc();
        $out = F::find(fn($v, $k) => $k === "l", $it);
        $this->assertSame($out, 40);
    }

    function testIncludes()
    {
        $arr = [1,2,3,4,5];
        $out1 = F::includes(1, $arr);
        $this->assertSame($out1, true);

        $out2 = F::includes(6, $arr);
        $this->assertSame($out2, false);
    }

    function testIncludesAll()
    {
        $arr = [1,2,3,4,5];
        $out1 = F::includesAll([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = F::includesAll([4,5,6], $arr);
        $this->assertSame($out2, false);
        $out3 = F::includesAll([5,6,7], $arr);
        $this->assertSame($out3, false);
        $out4 = F::includesAll([6,7,8], $arr);
        $this->assertSame($out4, false);
    }

    function testIncludesAny()
    {
        $arr = [1,2,3,4,5];
        $out1 = F::includesAny([3,4,5], $arr);
        $this->assertSame($out1, true);
        $out2 = F::includesAny([4,5,6], $arr);
        $this->assertSame($out2, true);
        $out3 = F::includesAny([5,6,7], $arr);
        $this->assertSame($out3, true);
        $out4 = F::includesAny([6,7,8], $arr);
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

        $res = F::groupBy(fn($v) => F::prop("id", $v), $arr);

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