<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class MergeAllLeftTest extends TestCase
{
    public function testMerge()
    {
        $out1 = F::mergeAllLeft(["a" => 1, "b" => 2], ["a" => 3, "c" => 4]);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 4], $out1);

        $out2 = F::mergeAllLeft((object)["a" => 1, "b" => 2],(object)["a" => 3, "c" => 4]);
        $this->assertEquals((object)["a" => 1, "b" => 2, "c" => 4], $out2);
    }
    
    public function testNoInputs()
    {
        $out = F::mergeAllLeft();
        $this->assertTrue($out instanceof \stdClass);
        $this->assertEquals(new \stdClass(), $out);
    }
    
    public function testMixedInputs()
    {
        $out1 = F::mergeAllLeft((object)["c" => 3, "d" => "dog"], (object)["a" => 5, "c" => "cat"], ["a" => 1, "b" => 2]);
        $this->assertEquals(["a" => 5, "b" => 2, "c" => 3, "d" => "dog"], $out1);
        
        $out2 = F::mergeAllLeft(["mouse" => 5, "cat" => 1], ["hamster" => 2], (object)["cat" => 7, "dog" => 3]);
        $this->assertEquals((object)["cat" => 1, "dog" => 3, "mouse" => 5, "hamster" => 2], $out2);
    }
    
    public function testDefer()
    {
        $obj = new class() {
            public $params = null;
            public function mergeAllLeft(...$args)
            {
                $this->params = $args;
                return ["hello" => "world"];
            }
        };
        
        $out = F::mergeAllLeft(["a" => 1, "b" => 2], (object)["c" => 3], $obj);
        $this->assertEquals(["a" => 1, "b" => 2], $obj->params[0]);
        $this->assertEquals((object)["c" => 3], $obj->params[1]);
        $this->assertEquals(["hello" => "world"], $out);
    }
}