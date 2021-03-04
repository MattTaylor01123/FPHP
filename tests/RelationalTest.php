<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class RelationalTest extends TestCase
{
    public function testLt()
    {
        $this->assertTrue(F::lt(6, 7));
        $this->assertFalse(F::lt(6, 6));
        $this->assertFalse(F::lt(6, 5));
        
        $lt7 = F::lt(F::__(), 7);
        $this->assertTrue($lt7(6));
    }
    
    public function testLte()
    {
        $this->assertTrue(F::lte(6, 7));
        $this->assertTrue(F::lte(6, 6));
        $this->assertFalse(F::lte(6, 5));
        
        $lte7 = F::lte(F::__(), 7);
        $this->assertTrue($lte7(6));
    }
    
    public function testGt()
    {
        $this->assertTrue(F::gt(7, 6));
        $this->assertFalse(F::gt(6, 6));
        $this->assertFalse(F::gt(5, 6));
        
        $gt5 = F::gt(F::__(), 5);
        $this->assertTrue($gt5(6));
    }
    
    public function testGte()
    {
        $this->assertTrue(F::gte(7, 6));
        $this->assertTrue(F::gte(6, 6));
        $this->assertFalse(F::gte(5, 6));
        
        $gte5 = F::gte(F::__(), 5);
        $this->assertTrue($gte5(6));
    }
    
    public function testEq()
    {
        $this->assertTrue(F::eq(3, 3));
        $this->assertTrue(F::eq("hello", "hello"));
        $this->assertTrue(F::eq(true, true));
        $this->assertFalse(F::eq(3, 5));
        
        $eq5 = F::eq(5);
        $this->assertTrue($eq5(5));
        $this->assertFalse($eq5(3));
    }
    
    public function testNeq()
    {
        $this->assertFalse(F::neq(3, 3));
        $this->assertFalse(F::neq("hello", "hello"));
        $this->assertFalse(F::neq(true, true));
        $this->assertTrue(F::neq(3, 5));
        
        $neq5 = F::neq(5);
        $this->assertFalse($neq5(5));
        $this->assertTrue($neq5(3));
    }
}