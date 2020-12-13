<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class RamdaPHPTest extends TestCase
{
    use TestUtils;

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
