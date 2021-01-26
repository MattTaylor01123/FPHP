<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class ConcatTest extends TestCase
{
    public function testConcat()
    {
        $arr = [1,2,3];
        $out = R::concat($arr, 4);
        $this->assertEquals([1,2,3,4], $out);
        $this->assertEquals([1,2,3], $arr);
        $this->assertNotSame($arr, $out);
    }
}