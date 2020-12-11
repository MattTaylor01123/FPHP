<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class RamdaPHPTest extends TestCase
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


    public function testFlatten()
    {
        $arr = [
            [
                "bob",
                "steve"
            ],
            "lucy",
            "laura",
            [
                "janet",
                [
                    "barbara"
                ]
            ]
        ];

        $res = R::flatten($arr);

        $this->assertEquals($res, [
            "bob",
            "steve",
            "lucy",
            "laura",
            "janet",
            [
                "barbara"
            ]
        ]);
    }
}
