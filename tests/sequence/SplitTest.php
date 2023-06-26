<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

class SplitTest extends TestCase
{
    public function testConsecutive()
    {
        $out = F::split(4, 0, ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"]);        
        $this->assertEquals([["a", "b", "c", "d"], ["e", "f", "g", "h"]], $out);
    }
    
    public function testGap()
    {
        $out = F::split(4, 2, ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"]);
        $this->assertEquals([["a", "b", "c", "d"], ["g", "h", "i", "j"]], $out);
    }
    
    public function testOverlap()
    {
        $out = F::split(4, -3, ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"]);
        $this->assertEquals([["a", "b", "c", "d"], 
                             ["b", "c", "d", "e"], 
                             ["c", "d", "e", "f"], 
                             ["d", "e", "f", "g"],
                             ["e", "f", "g", "h"],
                             ["f", "g", "h", "i"],
                             ["g", "h", "i", "j"]], $out);
    }
    
    public function testThread()
    {
        $fn = F::split(4, 0);
        $this->assertTrue(is_callable($fn));
        $out = $fn(["a", "b", "c", "d", "e", "f", "g", "h", "i", "j"]);        
        $this->assertEquals([["a", "b", "c", "d"], ["e", "f", "g", "h"]], $out);
    }
}
