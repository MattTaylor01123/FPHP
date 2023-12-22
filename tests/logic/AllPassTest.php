<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests\logic;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class AllPassTest extends TestCase
{    
    public function testAllPass()
    {
        $fn = F::allPass(F::isArray(), fn($coll) => F::all(F::isInteger(), $coll));
        
        $this->assertTrue($fn([1,2,3,4,5]));
        $this->assertFalse($fn([1,2,3,4, "hello"]));
        $this->assertFalse($fn(12345));
    }
}