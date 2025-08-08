<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AssocTest extends TestCase
{
    public function testAssocObj()
    {
        $obj = new stdClass();
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
}