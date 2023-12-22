<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class LogicalTest extends TestCase
{
    public function testAnyPass()
    {
        $fn = F::anyPass(F::isInteger(), F::isString());
        
        $this->assertTrue($fn(1));
        $this->assertTrue($fn("hello"));
        $this->assertFalse($fn(true));
        $this->assertFalse($fn([]));
    }
}