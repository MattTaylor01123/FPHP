<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\logic;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class AnyTest extends TestCase
{    
    public function testAny()
    {
        $this->assertFalse(F::any(fn($v) => $v > 5, [1,2,3,4,5]));
        $this->assertTrue(F::any(fn($v) => $v > 5, [1,2,3,4,5,6]));
    }
}
