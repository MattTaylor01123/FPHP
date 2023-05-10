<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

class FlattenTest extends TestCase
{
    public function testFlatten()
    {
        $in = [
            "Hello",
            "my",
            "name",
            "is",
            ["first" => "Steve", "last" => "Smith"],
            "last" => "end"
        ];
        
        $out = F::flatten($in);
        $this->assertEquals([
            "Hello", "my", "name", "is", "Steve", "Smith", "end"
        ], $out);
    }

    public function testFlatMap()
    {
        $in = [
            "Hello",
            "my",
            "name",
            "is",
            ["first" => "Steve", "last" => "Smith"],
            "last" => "end"
        ];
        
        $out = F::flatMap(fn($v) => strtoupper(is_array($v) ? implode("-", $v) : $v), $in);
        $this->assertEquals([
            "HELLO", "MY", "NAME", "IS", "STEVE-SMITH", "END"
        ], $out);
    }
    
    public function testThreading()
    {
        $in = [
            "Hello",
            "my",
            "name",
            "is",
            ["first" => "Steve", "last" => "Smith"],
            "last" => "end"
        ];
        
        $fn = F::flatten();
        $this->assertTrue(is_callable($fn));
        $out = $fn($in);
        
        $this->assertEquals([
            "Hello", "my", "name", "is", "Steve", "Smith", "end"
        ], $out);
        
        $in2 = [
            "Hello",
            "my",
            "name",
            "is",
            ["first" => "Steve", "last" => "Smith"],
            "last" => "end"
        ];
        
        $fn2 = F::flatMap(fn($v) => strtoupper(is_array($v) ? implode("-", $v) : $v));
        $this->assertTrue(is_callable($fn2));
        $out2 = $fn2($in2);
        
        $this->assertEquals([
            "HELLO", "MY", "NAME", "IS", "STEVE-SMITH", "END"
        ], $out2);
    }
}
