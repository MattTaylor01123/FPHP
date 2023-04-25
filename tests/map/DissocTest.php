<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;

final class DissocTest extends TestCase
{
    public function testDissocObject()
    {
        $obj = (object)["name" => "Matt", "age" => 47];
        $out = F::dissoc($obj, "age");
        $this->assertEquals((object)["name" => "Matt"], $out);
    }
    
    public function testDissocArray()
    {
        $obj = ["name" => "Matt", "age" => 47];
        $out = F::dissoc($obj, "age");
        $this->assertEquals(["name" => "Matt"], $out);        
    }
    
    public function testAssocCusType()
    {
        $obj = new TestType();
        $obj->a = 5;
        $obj->b = "h";
        $obj2 = F::dissoc($obj, "b");

        $exp = new TestType();
        $exp->a = 5;
        $this->assertTrue($obj2 instanceof TestType);
        $this->assertEquals($exp, $obj2);
    }
}
