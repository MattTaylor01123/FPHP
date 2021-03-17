<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class TakeWhileTest extends TestCase
{
    use TestUtils;
    
    public function testTakeWhile()
    {
        $pred = function($v, $k)
        {
            return $v <= 3;
        };
        
        $res = F::takeWhile($pred, $this->getIndexedArray());
        
        $this->assertSame([1, 2, 3], $res);
        
        $count = 0;
        $pred2 = function($v, $k) use(&$count)
        {
            $count++;
            return $v <= 30;
        };
        
        $res2 = F::takeWhile($pred2, $this->getItIdx());
        
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
}
