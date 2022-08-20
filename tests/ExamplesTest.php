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
        $this->assertEquals((object)["a" => 2, "b" => 4, "c" => 6], $actObj);
    }

    public function testExample2()
    {
        // Given the following transformation function...
        $count = 0;
        $fnTransform = F::pipe(
            F::map(function($v) use(&$count) {
                $count++;
                return $v * 2;
            }),
            F::take(3)
        );

        // When the function is applied to an array or object, it behaves as
        // normal (no laziness)
        $count = 0;
        $assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actAssocArr = $fnTransform($assocArr);
        $this->assertEquals(5, $count);
        $this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $actAssocArr);
        $this->assertEquals(5, $count);

        // When the function is applied to an iterator or generator, we get
        // lazy behaviour.
        // Although the function is called to produce $actGen, $actGen only gets
        // calculated when it is used.
        // In the code below, it's used in the iterator_to_array function
        $count = 0;
        $gen = fn() => yield from ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actGen = $fnTransform($gen());
        $this->assertEquals(0, $count);
        $this->assertEquals(["a" => 2, "b" => 4, "c" => 6], iterator_to_array($actGen));
        $this->assertEquals(3, $count);
    }
}