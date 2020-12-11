<?php

/* 
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class LogicalTest extends TestCase
{
    public function testAllPass()
    {
        $fn = R::allPass(R::isArray(), R::all(R::isInteger()));
        
        $this->assertTrue($fn([1,2,3,4,5]));
        $this->assertFalse($fn([1,2,3,4, "hello"]));
        $this->assertFalse($fn(12345));
    }

    public function testAnyPass()
    {
        $fn = R::anyPass(R::isInteger(), R::isString());
        
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
        $this->assertSame(R::andd($v1, $v2), ($v1 && $v2));
    }
    
    /**
     * @dataProvider booleanCases
     */
    public function testOrr($v1, $v2)
    {
        $this->assertSame(R::orr($v1, $v2), ($v1 || $v2));
    }
    
    public function testNot()
    {
        $this->assertSame(R::not(false), true);
        $this->assertSame(R::not(true), false);
    }
}