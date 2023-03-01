<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

class FindTest extends TestCase
{
    public function testFindFirst()
    {
        $in = [1,2,3,4,5];
        $out = F::findFirst(fn($v) => $v > 3, $in);
        $this->assertEquals(4, $out);
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findFirst(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals(2, $out2);
    }
    
    public function testFindFirstK()
    {
        $in = [1,2,3,4,5];
        $out = F::findFirstK(fn($v) => $v > 3, $in);
        $this->assertEquals(3, $out);
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findFirstK(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals("aa", $out2);
    }
    
    public function testFindLast()
    {
        $in = [1,2,3,4,5];
        $out = F::findLast(fn($v) => $v > 3, $in);
        $this->assertEquals(5, $out);
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findLast(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals(4, $out2);
    }
    
    public function testFindLastK()
    {
        $in = [1,2,3,4,5];
        $out = F::findLastK(fn($v) => $v > 3, $in);
        $this->assertEquals(4, $out);
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findLastK(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals("abc", $out2);
    }
}