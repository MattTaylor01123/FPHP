<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class ExamplesTest extends TestCase
{
    public function testExample1()
    {
        $fnTransform = F::pipe(
            F::map(fn($v) => $v * 2),
            F::take(3)
        );

        $arr = [1,2,3,4,5];
        $resArr = $fnTransform($arr);
        $this->assertEquals([2,4,6], $resArr);

        $assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actAssocArr = $fnTransform($assocArr);
        $this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $actAssocArr);

        $obj = (object)["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actObj = $fnTransform($obj);
        $this->assertEquals((object)["a" => 2, "b" => 4, "c" => 6], $actAssocArr);
    }
}