<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class EqualsTest extends TestCase
{
    public function equalsProvider()
    {
        return [
            // scalar types ----------------------------------------------------
            [10, 10, true],
            [10, 9, false],
            [10, "10", false],
            [10.5, 10.5, true],
            [10.5, 10.4, false],
            [10.5, "10.5", false],
            [true, true, true],
            [true, false, false],
            [true, "true", false],
            ["hello", "hello", true],
            ["hello", "world", false],
            ["hello", "HELLO", false],
            ["hello", false, false],
            // arrays ----------------------------------------------------------
            [[1,2,3], [1,2,3], true],
            [[1,2,3], [1,3,2], false],
            [["a" => 1, "b" => 2, "c" => 3], ["a" => 1, "b" => 2, "c" => 3], true],
            [["a" => 1, "b" => 2, "c" => 3], ["a" => 1, "c" => 3, "b" => 2], true],
            [[1], [1, 2], false],
            [["a" => 1], ["a" => 1, "b" => 2], false],
            // objects ---------------------------------------------------------
            [(object)["a" => 1, "b" => 2, "c" => 3], (object)["a" => 1, "b" => 2, "c" => 3], true],
            [(object)["a" => 1, "b" => 2, "c" => 3], (object)["a" => 1, "c" => 3, "b" => 2], true],
            [(object)["a" => 1, "b" => 2, "c" => 3], (object)["a" => 1, "b" => 2, "d" => 3], false],
            [(object)["a" => 1, "b" => 2, "c" => 3], (object)["a" => 1, "b" => 2], false],
            // nested structures -----------------------------------------------
            [[[1,2],[3,4]],[[1,2],[3,4]], true],
            [[[1,2],[3,4]],[[1,2],[3,5]], false],
            [(object)["a" => 1, "b" => [1,2,3]], (object)["a" => 1, "b" => [1,2,3]], true],
            [(object)["a" => 1, "b" => [1,2,3]], (object)["a" => 1, "b" => [1,2,4]], false]
        ];
    }
    
    /**
     * @dataProvider equalsProvider
     */
    public function testEquals($v1, $v2, $expected)
    {
        $res = F::equals($v1, $v2);
        $this->assertEquals($res, $expected);
    }
}
