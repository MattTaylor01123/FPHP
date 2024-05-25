<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\logic;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class AllTest extends TestCase
{    
    public function testAll()
    {
        $this->assertTrue(F::all(fn($v) => $v <= 5, [1,2,3,4,5]));
        $this->assertFalse(F::all(fn($v) => $v <= 5, [1,2,3,4,5,6]));
    }
}