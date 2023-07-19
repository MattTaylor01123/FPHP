<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use stdClass;

class AssocPathTest extends TestCase
{
    public function testObject()
    {
        $out = F::assocPath(["this", "is", "a", "test"], 123, new stdClass());
        $exp = (object)["this" => (object)["is" => (object)["a" => (object)["test" => 123]]]];
        $this->assertEquals($exp, $out);
    }
    
    public function testArray()
    {
        $out = F::assocPath(["this", "is", "a", "test"], 123, []);
        $exp = ["this" => (object)["is" => (object)["a" => (object)["test" => 123]]]];
        $this->assertEquals($exp, $out);        
    }
    
    public function testIndexedArray()
    {
        $out = F::assocPath(["this", "is", 0, "test"], 123, new stdClass());
        $exp = (object)["this" => (object)["is" => [(object)["test" => 123]]]];
        $this->assertEquals($exp, $out);
    }
    
    public function testOverwrite()
    {
        $in = (object)["name" => "Fred", "address" => (object)["city" => "Birmingham", "postCode" => "BR1 2IM"]];
        $out = F::assocPath(["address", "city"], "London", $in);
        $exp = clone $in;
        $exp->address->city = "London";
        $this->assertEquals($exp, $out);
        $this->assertEquals("London", $out->address->city);
    }
    
    public function testThreadable()
    {
        $fn = F::assocPath(["cars"], 7);
        $this->assertTrue(is_callable($fn));
        $out = $fn((object)["name" => "Bob", "cars" => 3]);
        $this->assertEquals((object)["name" => "Bob", "cars" => 7], $out);
    }
}
