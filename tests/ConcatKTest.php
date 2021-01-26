<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class ConcatKTest extends TestCase
{
    public function testConcatK()
    {
        $arr = ["a" => 1, "b" => 2];
        $out = R::concatK($arr, 3, "c");
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3], $out);
        $this->assertEquals(["a" => 1, "b" => 2], $arr);
        $this->assertNotSame($arr, $out);
    }
}