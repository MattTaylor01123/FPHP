<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class PipingTest extends TestCase 
{
    function testPipe()
    {
        $nand = R::pipe(
            R::andd(),
            R::not()
        );
        
        $this->assertSame($nand(false, false), true);
        $this->assertSame($nand(true, false), true);
        $this->assertSame($nand(true, true), false);
    }
    
    function testPipex()
    {
        $filterRes = R::pipex(
            [1,2,3,4,5,6],
            R::joinUp("-")
        );
        
        $this->assertSame($filterRes, "1-2-3-4-5-6");
    }
}
