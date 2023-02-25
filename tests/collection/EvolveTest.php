<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class EvolveTest extends TestCase
{
    public function testBasic()
    {
        $in = (object)["a" => 1, "b" => 2, "c" => "Hello"];
        $out = F::evolve([
            "a" => fn($v) => ++$v,
            "c" => fn($v) => strtoupper($v)
        ], $in);

        $this->assertEquals((object)["a" => 2, "b" => 2, "c" => "HELLO"], $out);
    }

    public function testArray()
    {
        $in = ["a" => 1, "b" => 2, "c" => "Hello"];
        $out = F::evolve([
            "a" => fn($v) => ++$v,
            "c" => fn($v) => strtoupper($v)
        ], $in);

        $this->assertEquals(["a" => 2, "b" => 2, "c" => "HELLO"], $out);
    }

    public function testMissingFields()
    {
        $in = (object)["b" => 2, "c" => "Hello"];
        $out = F::evolve([
            "a" => fn($v) => ++$v,
            "c" => fn($v) => strtoupper($v)
        ], $in);

        $this->assertEquals((object)["b" => 2, "c" => "HELLO"], $out);
    }
    
    public function testThreading()
    {
        $in = (object)["a" => 1, "b" => 2, "c" => "Hello"];
        $fn = F::evolve([
            "a" => fn($v) => ++$v,
            "c" => fn($v) => strtoupper($v)
        ]);
        $this->assertTrue(is_callable($fn));
        
        $out = $fn($in);

        $this->assertEquals((object)["a" => 2, "b" => 2, "c" => "HELLO"], $out);
    }
}