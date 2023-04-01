<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use src\utilities\IterableGenerator;
use tests\TestUtils;

final class TakeWhileTest extends TestCase
{
    use TestUtils;
    
    public function testTakeWhile()
    {
        $pred = fn($v) => $v <= 3;       
        $res = F::takeWhile($pred, [1,2,3,4,5]);
        $this->assertEquals([1, 2, 3], $res);
        
        $pred3 = fn($v, $k) => in_array($k, ["a", "b"]);
        $res3 = F::takeWhile($pred3, ["a" => 1, "b" => 2, "c" => 3, "d" => 4]);
        $this->assertEquals(["a" => 1, "b" => 2], $res3);
        
        $count = 0;
        $pred2 = function($v) use(&$count)
        {
            $count++;
            return $v <= 30;
        };
        
        $v2 = F::generatorToIterable(fn() => yield from [10, 20, 30, 40]);       
        $res2 = F::takeWhile($pred2, $v2);
        
        $i = 0;
        $exp = [10, 20, 30];
        $this->assertEquals(0, $count);
        foreach($res2 as $v)
        {
            $this->assertEquals($exp[$i], $v);
            $this->assertEquals($i + 1, $count);
            $i++;
        }
        
        $this->assertEquals($exp, iterator_to_array($res2, false));
    }
    
    public function testTakeWhileT()
    {
        $v1 = [1,2,3,4,5];
        $pred = fn($v) => $v <= 3;
        $out = F::into([], F::takeWhileT($pred), $v1);
        $this->assertEquals([1,2,3], $out);
    }

    public function testLazyness()
    {
        $count = 0;
        $fn = function($v) use(&$count) {
            $count = $count + 1;
            return $v > 10;
        };

        $count2 = 0;
        $fn2 = function($v) use(&$count2) {
            $count2 = $count2 + 1;
            return $count2 < 2;
        };

        $out = F::pipex($this->getItIdx(),
            fn($c) => F::filter($fn, $c),
            fn($c) => F::takeWhile($fn2, $c)
        );

        // no iteration has occurred yet
        $this->assertEquals(0, $count);
        $this->assertEquals(0, $count2);

        $vals = array();
        $i = 0;
        foreach($out as $v)
        {
            $i = $i + 1;
            $this->assertEquals($i + 1, $count);
            $this->assertEquals($i, $count2);
            $vals[] = $v;
        }

        $this->assertEquals(3, $count);
        $this->assertEquals(2, $count2);
        $this->assertEquals([20], $vals);
    }
    
    public function testTakeWhileThreadable()
    {
        $fn = F::takeWhile(fn($v) => $v > 3);
        $this->assertTrue(is_callable($fn));
        $out = $fn([5,4,3,2,1]);
        $this->assertEquals([5,4], $out);
    }
}
