<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class ReadmeTest extends TestCase
{
    public function test1()
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
    }
    
    public function test2()
    {
        $count = 0;
        $fnTransform = F::pipe(
            F::map(function($v) use(&$count) {
                $count++;
                return $v * 2;
            }),
            F::take(3)
        );
            
        $count = 0;
        $assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actAssocArr = $fnTransform($assocArr);
        $this->assertEquals(5, $count);
        $this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $actAssocArr);
        $this->assertEquals(5, $count);
    }
    
    public function test3()
    {
        $count = 0;
        $fnTransform = F::pipe(
            F::map(function($v) use(&$count) {
                $count++;
                return $v * 2;
            }),
            F::take(3)
        );
        
        $count = 0;
        $gen = fn() => yield from ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
        $actGen = $fnTransform($gen());
        $this->assertEquals(0, $count);
        $this->assertEquals(["a" => 2, "b" => 4, "c" => 6], iterator_to_array($actGen));
        $this->assertEquals(3, $count);
    }
     
    function test4()
    {
        $count = 0;
        $fnTransformT = F::pipe(
            F::mapT(function($v) use(&$count) {
                $count++;
                return $v * 2;
            }),
            F::takeT(3)
        );
        $assocArr = [1, 2, 3, 4, 5];
        $out = F::transduce($fnTransformT, fn($acc, $v, $k) => F::append($acc, $v, $k), [], $assocArr);

        $this->assertEquals(3, $count);
        $this->assertEquals([2,4,6], $out);
        $this->assertEquals(3, $count);
    }
}
