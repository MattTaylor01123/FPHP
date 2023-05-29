<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

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
        
        $out3 = F::findFirst(fn($v, $k) => $k === "abcd", $in2);
        $this->assertEquals(null, $out3);
    }
    
    public function testFindFirstIndex()
    {
        $in = [1,2,3,4,5];
        $out = F::findFirstIndex(fn($v) => $v > 3, $in);
        $this->assertEquals(3, $out);
        
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findFirstIndex(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals("aa", $out2);
        
        $out3 = F::findFirstIndex(fn($v, $k) => $k === "abcd", $in2);
        $this->assertEquals(-1, $out3);
    }
    
    public function testFindLast()
    {
        $in = [1,2,3,4,5];
        $out = F::findLast(fn($v) => $v > 3, $in);
        $this->assertEquals(5, $out);
        
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findLast(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals(4, $out2);
        
        $out3 = F::findLast(fn($v, $k) => $k === "abcd", $in2);
        $this->assertEquals(null, $out3);
    }
    
    public function testFindLastIndex()
    {
        $in = [1,2,3,4,5];
        $out = F::findLastIndex(fn($v) => $v > 3, $in);
        $this->assertEquals(4, $out);
        
        $in2 = ["a" => 1, "aa" => 2, "ab" => 3, "abc" => 4];
        $out2 = F::findLastIndex(fn($v, $k) => strlen($k) >= 2, $in2);
        $this->assertEquals("abc", $out2);
        
        $out3 = F::findLastIndex(fn($v, $k) => $k === "abcd", $in2);
        $this->assertEquals(-1, $out3);
    }
}