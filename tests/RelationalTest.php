<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\Core as C;

final class RelationalTest extends TestCase
{
    public function testLt()
    {
        $this->assertTrue(C::lt(6, 7));
        $this->assertFalse(C::lt(6, 6));
        $this->assertFalse(C::lt(6, 5));
        
        $lt7 = C::lt(C::__(), 7);
        $this->assertTrue($lt7(6));
    }
    
    public function testLte()
    {
        $this->assertTrue(C::lte(6, 7));
        $this->assertTrue(C::lte(6, 6));
        $this->assertFalse(C::lte(6, 5));
        
        $lte7 = C::lte(C::__(), 7);
        $this->assertTrue($lte7(6));
    }
    
    public function testGt()
    {
        $this->assertTrue(C::gt(7, 6));
        $this->assertFalse(C::gt(6, 6));
        $this->assertFalse(C::gt(5, 6));
        
        $gt5 = C::gt(C::__(), 5);
        $this->assertTrue($gt5(6));
    }
    
    public function testGte()
    {
        $this->assertTrue(C::gte(7, 6));
        $this->assertTrue(C::gte(6, 6));
        $this->assertFalse(C::gte(5, 6));
        
        $gte5 = C::gte(C::__(), 5);
        $this->assertTrue($gte5(6));
    }
    
    public function testEq()
    {
        $this->assertTrue(C::eq(3, 3));
        $this->assertTrue(C::eq("hello", "hello"));
        $this->assertTrue(C::eq(true, true));
        $this->assertFalse(C::eq(3, 5));
        
        $eq5 = C::eq(5);
        $this->assertTrue($eq5(5));
        $this->assertFalse($eq5(3));
    }
    
    public function testNeq()
    {
        $this->assertFalse(C::neq(3, 3));
        $this->assertFalse(C::neq("hello", "hello"));
        $this->assertFalse(C::neq(true, true));
        $this->assertTrue(C::neq(3, 5));
        
        $neq5 = C::neq(5);
        $this->assertFalse($neq5(5));
        $this->assertTrue($neq5(3));
    }
}