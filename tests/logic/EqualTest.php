<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests\logic;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class EqualTest extends TestCase
{    
    public function testEq()
    {
        $this->assertTrue(F::eq(3, 3));
        $this->assertTrue(F::eq("hello", "hello"));
        $this->assertTrue(F::eq(true, true));
        $this->assertFalse(F::eq(3, 5));
    }
}