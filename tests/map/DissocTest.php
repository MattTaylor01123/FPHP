<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

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
}
