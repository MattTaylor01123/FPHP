<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class MergeLeftTest extends TestCase
{
    public function testMerge()
    {
        $out1 = F::mergeLeft(["a" => 1, "b" => 2], ["a" => 3, "c" => 4]);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 4], $out1);

        $out2 = F::mergeLeft((object)["a" => 1, "b" => 2],(object)["a" => 3, "c" => 4]);
        $this->assertEquals((object)["a" => 1, "b" => 2, "c" => 4], $out2);
    }
    
    public function testMixedInputs()
    {
        $out1 = F::mergeLeft((object)["c" => 3, "d" => "dog"], ["a" => 1, "b" => 2, "c" => 1]);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "d" => "dog"], $out1);
        
        $out2 = F::mergeLeft(["mouse" => 5, "cat" => 1], (object)["cat" => 7, "dog" => 3]);
        $this->assertEquals((object)["cat" => 1, "dog" => 3, "mouse" => 5], $out2);
    }
    
    public function testDefer()
    {
        $obj = new class() {
            public $params = null;
            public function mergeLeft(...$args)
            {
                $this->params = $args;
                return ["hello" => "world"];
            }
        };
        
        $out = F::mergeLeft(["a" => 1, "b" => 2], $obj);
        $this->assertEquals([["a" => 1, "b" => 2]], $obj->params);
        $this->assertEquals(["hello" => "world"], $out);
    }
}