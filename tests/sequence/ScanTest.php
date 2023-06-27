<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

class ScanTest extends TestCase
{
    public function testArray()
    {
        $out = F::scan(fn($acc, $v) => $acc + $v, 0, [1,2,3,4,5]);
        $this->assertEquals([1,3,6,10,15], $out);
    }
    
    public function testAssocArray()
    {
        $out = F::scan(fn($acc, $v) => $acc + $v, 0, ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $this->assertEquals(["a" => 1, "b" => 3, "c" => 6, "d" => 10, "e" => 15], $out);
    }
    
    public function testIterable()
    {
        $iter = F::generatorToIterable(fn() => yield from [1,2,3,4,5]);
        $out = F::scan(fn($acc, $v) => $acc + $v, 0, $iter);
        $this->assertEquals([1,3,6,10,15], iterator_to_array($out));
    }
    
    public function testIterableK()
    {
        $iter = F::generatorToIterable(fn() => yield from ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5]);
        $out = F::scan(fn($acc, $v) => $acc + $v, 0, $iter);
        $this->assertEquals(["a" => 1, "b" => 3, "c" => 6, "d" => 10, "e" => 15], iterator_to_array($out));
    }
}
