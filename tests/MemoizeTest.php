<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\RamdaPHP as R;
use PHPUnit\Framework\TestCase;

final class MemoizeTest extends TestCase 
{
    function testMemoize()
    {
        $fn = function($val)
        {
            static $i = 0;
            $i++;
            return $i;
        };
        
        $mfn = R::memoize($fn);
        
        $out1 = $mfn("a");
        $this->assertSame($out1, 1);
        $out2 = $mfn("b");
        $this->assertSame($out2, 2);
        $out3 = $mfn("a");
        $this->assertSame($out3, 1);
    }
    
    public function testNoParams()
    {
        $fn = function()
        {
            static $i = 0;
            $i++;
            return $i;
        };
        
        $mfn = R::memoize($fn);
        $this->assertSame($mfn(), 1);
        $this->assertSame($mfn(), 1);
        
    }
}
