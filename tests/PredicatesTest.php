<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\Core as C;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PredicatesTest extends TestCase
{
    public function testIsInteger()
    {
        $this->assertTrue(C::isInteger(1));
    }

    public function testIsArray()
    {
        $this->assertTrue(C::isArray([]));
    }

    public function testIsBool()
    {
        $this->assertTrue(C::isBool(true));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(C::isEmpty([]));
        $this->assertFalse(C::isEmpty([1]));
        $this->assertFalse(C::isEmpty(["a" => 1]));
        $this->assertTrue(C::isEmpty((object)[]));
        $this->assertFalse(C::isEmpty((object)["a" => 1]));
        $this->assertFalse(C::isEmpty(null));
        $this->assertTrue(C::isEmpty(""));
        $this->assertFalse(C::isEmpty("a"));
    }

    public function testIsFloat()
    {
        $this->assertTrue(C::isFloat(1.03));
    }

    public function testIsObject()
    {
        $this->assertTrue(C::isObject(new stdClass()));
    }

    public function testIsString()
    {
        $this->assertTrue(C::isString("hello"));
    }

    // can't be named after the actual function's name as this breaks
    // PHPUnit
    public function testRegEx()
    {
        $this->assertTrue(C::test("/hello/", "hello"));
        $this->assertFalse(C::test("/hello/", "world"));

        $testHello = C::test("/hello/");
        $this->assertTrue($testHello("hello"));
    }

    public function testIsSequentialArray()
    {
        $v1 = [
            "hello", "world", "jump"
        ];
        $v2 = [
            "x" => 3,
            "y" => 4,
            "z" => 5
        ];

        $this->assertSame(C::isSequentialArray($v1), true);
        $this->assertSame(C::isSequentialArray($v2), false);
    }
}