<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class RelationalTest extends TestCase
{
    public function testLt()
    {
        $this->assertTrue(R::lt(6, 7));
        $this->assertFalse(R::lt(6, 6));
        $this->assertFalse(R::lt(6, 5));
        
        $lt7 = R::lt(R::__(), 7);
        $this->assertTrue($lt7(6));
    }
    
    public function testLte()
    {
        $this->assertTrue(R::lte(6, 7));
        $this->assertTrue(R::lte(6, 6));
        $this->assertFalse(R::lte(6, 5));
        
        $lte7 = R::lte(R::__(), 7);
        $this->assertTrue($lte7(6));
    }
    
    public function testGt()
    {
        $this->assertTrue(R::gt(7, 6));
        $this->assertFalse(R::gt(6, 6));
        $this->assertFalse(R::gt(5, 6));
        
        $gt5 = R::gt(R::__(), 5);
        $this->assertTrue($gt5(6));
    }
    
    public function testGte()
    {
        $this->assertTrue(R::gte(7, 6));
        $this->assertTrue(R::gte(6, 6));
        $this->assertFalse(R::gte(5, 6));
        
        $gte5 = R::gte(R::__(), 5);
        $this->assertTrue($gte5(6));
    }
    
    public function testEq()
    {
        $this->assertTrue(R::eq(3, 3));
        $this->assertTrue(R::eq("hello", "hello"));
        $this->assertTrue(R::eq(true, true));
        $this->assertFalse(R::eq(3, 5));
        
        $eq5 = R::eq(5);
        $this->assertTrue($eq5(5));
        $this->assertFalse($eq5(3));
    }
    
    public function testNeq()
    {
        $this->assertFalse(R::neq(3, 3));
        $this->assertFalse(R::neq("hello", "hello"));
        $this->assertFalse(R::neq(true, true));
        $this->assertTrue(R::neq(3, 5));
        
        $neq5 = R::neq(5);
        $this->assertFalse($neq5(5));
        $this->assertTrue($neq5(3));
    }
}