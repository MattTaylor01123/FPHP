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
        $d = fn($s) => intval(explode("/", $s)[0]);
        $m = fn($s) => intval(explode("/", $s)[1]);
        $y = fn($s) => intval(explode("/", $s)[2]);
        
        $fnValidDate = F::allPass(
            fn($o) => $d($o) >= 0 && $d($o) <= 31,
            fn($o) => $d($o) !== 31 || in_array($m($o), [1,3,5,7,8,10,12]),
            fn($o) => $d($o) !== 30 || in_array($m($o), [4,6,9,11]),
            fn($o) => $d($o) !== 28 || $m($o) === 2,
            fn($o) => $d($o) !== 29 || ($m($o) === 2 && $y($o) % 4 === 0),
        );
        
        $out1 = $fnValidDate("28/02/2024");
        $out2 = $fnValidDate("29/02/2024");
        $out3 = $fnValidDate("30/02/2024");
        $out4 = $fnValidDate("29/02/2025");
        
        $this->assertTrue($out1);
        $this->assertTrue($out2);
        $this->assertFalse($out3);
        $this->assertFalse($out4);
    }
}