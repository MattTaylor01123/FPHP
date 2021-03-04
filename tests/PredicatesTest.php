<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PredicatesTest extends TestCase
{
    public function testIsInteger()
    {
        $this->assertTrue(F::isInteger(1));
    }

    public function testIsArray()
    {
        $this->assertTrue(F::isArray([]));
    }

    public function testIsBool()
    {
        $this->assertTrue(F::isBool(true));
    }

    public function testIsEmpty()
    {
        $this->assertTrue(F::isEmpty([]));
        $this->assertFalse(F::isEmpty([1]));
        $this->assertFalse(F::isEmpty(["a" => 1]));
        $this->assertTrue(F::isEmpty((object)[]));
        $this->assertFalse(F::isEmpty((object)["a" => 1]));
        $this->assertFalse(F::isEmpty(null));
        $this->assertTrue(F::isEmpty(""));
        $this->assertFalse(F::isEmpty("a"));
    }

    public function testIsFloat()
    {
        $this->assertTrue(F::isFloat(1.03));
    }

    public function testIsObject()
    {
        $this->assertTrue(F::isObject(new stdClass()));
    }

    public function testIsString()
    {
        $this->assertTrue(F::isString("hello"));
    }

    // can't be named after the actual function's name as this breaks
    // PHPUnit
    public function testRegEx()
    {
        $this->assertTrue(F::test("/hello/", "hello"));
        $this->assertFalse(F::test("/hello/", "world"));

        $testHello = F::test("/hello/");
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

        $this->assertSame(F::isSequentialArray($v1), true);
        $this->assertSame(F::isSequentialArray($v2), false);
    }
}