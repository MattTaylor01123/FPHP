<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;
use tests\TestUtils;

final class HasPropsTest extends TestCase
{
    use TestUtils;

    public function testHasPropsObject()
    {
        $this->assertTrue(F::hasProps(["f", "g", "h"], (object)[
            "f" => 2,
            "g" => 4,
            "h" => 6
        ]));
        $this->assertFalse(F::hasProps(["f", "g", "h", "i"], (object)[
            "f" => 2,
            "g" => 4,
            "h" => 6
        ]));
    }

    public function testHasPropsArray()
    {
        $this->assertTrue(F::hasProps(["c", "d", "e"], [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]));
        $this->assertFalse(F::hasProps(["c", "d", "e", "f"], [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]));
    }

    public function testHasPropsCustType()
    {
        $v = new TestType();
        $v->a = 1;
        $v->b = 2;
        $v->c = 3;

        $this->assertTrue(F::hasProps(["a", "b", "c"], $v));
        $this->assertFalse(F::hasProps(["a", "b", "c", "d"], $v));
    }
    
    public function testThreadable()
    {
        $a = ["a" => 1, "b" => 2, "c" => 3];
        $b = ["b" => 2, "c" => 3, "d" => 4];
        $fn = F::hasProps(["a", "b", "c"]);
        
        $this->assertTrue($fn($a));
        $this->assertFalse($fn($b));
    }
}