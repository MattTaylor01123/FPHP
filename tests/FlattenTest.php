<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class FlattenTest extends TestCase
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

        $this->assertEquals([
            "bob",
            "steve",
            "lucy",
            "laura",
            "janet",
            [
                "barbara"
            ]
        ], $res);
    }
}
