<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\RamdaPHP as R;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PredicatesTest extends TestCase
{
    public function testIsInteger()
    {
        $this->assertTrue(R::isInteger(1));
    }

    public function testIsArray()
    {
        $this->assertTrue(R::isArray([]));
    }

    public function testIsBool()
    {
        $this->assertTrue(R::isBool(true));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(R::isEmpty([]));
        $this->assertFalse(R::isEmpty([1]));
        $this->assertFalse(R::isEmpty(["a" => 1]));
        $this->assertTrue(R::isEmpty((object)[]));
        $this->assertFalse(R::isEmpty((object)["a" => 1]));
        $this->assertFalse(R::isEmpty(null));
        $this->assertTrue(R::isEmpty(""));
        $this->assertFalse(R::isEmpty("a"));
    }

    public function testIsFloat()
    {
        $this->assertTrue(R::isFloat(1.03));
    }

    public function testIsObject()
    {
        $this->assertTrue(R::isObject(new stdClass()));
    }

    public function testIsString()
    {
        $this->assertTrue(R::isString("hello"));
    }

    // can't be named after the actual function's name as this breaks
    // PHPUnit
    public function testRegEx()
    {
        $this->assertTrue(R::test("/hello/", "hello"));
        $this->assertFalse(R::test("/hello/", "world"));

        $testHello = R::test("/hello/");
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

        $this->assertSame(R::isSequentialArray($v1), true);
        $this->assertSame(R::isSequentialArray($v2), false);
    }
}