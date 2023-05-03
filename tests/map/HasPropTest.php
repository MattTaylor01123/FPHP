<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;

final class HasPropTest extends TestCase
{
    public function testHasPropObject()
    {
        $this->assertTrue(F::hasProp("f", (object)[
            "f" => 2,
            "g" => 4,
            "h" => 6
        ]));
        $this->assertFalse(F::hasProp("i", (object)[
            "f" => 2,
            "g" => 4,
            "h" => 6
        ]));
    }

    public function testHasPropArray()
    {
        $this->assertTrue(F::hasProp("a", [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]));
        $this->assertFalse(F::hasProp("f", [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]));
    }

    public function testHasPropCustType()
    {
        $v = new TestType();
        $v->a = 1;

        $this->assertTrue(F::hasProp("a", $v));
        $this->assertFalse(F::hasProp("c", $v));
    }
}