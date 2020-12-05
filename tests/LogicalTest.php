<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\Core as C;

final class LogicalTest extends TestCase
{
    public function testAllPass()
    {
        $fn = C::allPass(C::isArray(), C::all(C::isInteger()));
        
        $this->assertTrue($fn([1,2,3,4,5]));
        $this->assertFalse($fn([1,2,3,4, "hello"]));
        $this->assertFalse($fn(12345));
    }

    public function testAnyPass()
    {
        $fn = C::anyPass(C::isInteger(), C::isString());
        
        $this->assertTrue($fn(1));
        $this->assertTrue($fn("hello"));
        $this->assertFalse($fn(true));
        $this->assertFalse($fn([]));
    }
    
    public function booleanCases()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false]
        ];
    }
    
    /**
     * @dataProvider booleanCases
     */
    public function testAndd($v1, $v2)
    {
        $this->assertSame(C::andd($v1, $v2), ($v1 && $v2));
    }
    
    /**
     * @dataProvider booleanCases
     */
    public function testOrr($v1, $v2)
    {
        $this->assertSame(C::orr($v1, $v2), ($v1 || $v2));
    }
    
    public function testNot()
    {
        $this->assertSame(C::not(false), true);
        $this->assertSame(C::not(true), false);
    }
}