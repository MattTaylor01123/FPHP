<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class LogicalTest extends TestCase
{
    public function testAllPass()
    {
        $fn = F::allPass(F::isArray(), fn($coll) => F::all(F::isInteger(), $coll));
        
        $this->assertTrue($fn([1,2,3,4,5]));
        $this->assertFalse($fn([1,2,3,4, "hello"]));
        $this->assertFalse($fn(12345));
    }

    public function testAnyPass()
    {
        $fn = F::anyPass(F::isInteger(), F::isString());
        
        $this->assertTrue($fn(1));
        $this->assertTrue($fn("hello"));
        $this->assertFalse($fn(true));
        $this->assertFalse($fn([]));
    }
}