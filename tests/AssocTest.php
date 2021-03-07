<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class AssocTest extends TestCase
{
    public function testAssocObj()
    {
        $obj = new \stdClass();
        $obj->a = 5;

        $obj2 = F::assoc($obj, 6, "b");

        $this->assertNotSame($obj, $obj2);
        $this->assertEquals((object)["a" => 5], $obj);
        $this->assertEquals((object)["a" => 5, "b" => 6], $obj2);
    }

    public function testAssocArr()
    {
        $arr = ["a" => 1];

        $arr2 = F::assoc($arr, 2, "b");

        $this->assertTrue(is_array($arr2));
        $this->assertEquals(["a" => 1, "b" => 2], $arr2);
    }

    public function testAssocCusType()
    {
        $obj = new TestType();
        $obj->a = 5;
        $obj->b = "h";
        $obj2 = F::assoc($obj, 15, "c");

        $exp = new TestType();
        $exp->a = 5;
        $exp->b = "h";
        $exp->c = 15;
        $this->assertTrue($obj2 instanceof TestType);
        $this->assertEquals($exp, $obj2);
    }
}