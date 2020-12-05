<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

require_once "../vendor/autoload.php";

// https://api.phpunit.de/


use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use RamdaPHP\Core as C;

final class CoreTest extends TestCase
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

        $res = C::flatten($arr);

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
