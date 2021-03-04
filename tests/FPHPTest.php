<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class FPHPTest extends TestCase
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

        $res = F::flatten($arr);

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
