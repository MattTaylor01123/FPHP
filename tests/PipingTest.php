<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\Core as C;

final class PipingTest extends TestCase 
{
    function testPipe()
    {
        $nand = C::pipe(
            C::andd(),
            C::not()
        );
        
        $this->assertSame($nand(false, false), true);
        $this->assertSame($nand(true, false), true);
        $this->assertSame($nand(true, true), false);
    }
    
    function testPipex()
    {
        $filterRes = C::pipex(
            [1,2,3,4,5,6],
            C::joinUp("-")
        );
        
        $this->assertSame($filterRes, "1-2-3-4-5-6");
    }
}
