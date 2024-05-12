<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class AppendTest extends TestCase
{
    // append

    public function testAppendArrayIdx()
    {
        $o = F::append([1,2,3,4,5], 6);
        $this->assertTrue(is_array($o));
        $this->assertEquals([1,2,3,4,5,6], $o);
    }

    public function testAppendArrayAssoc()
    {
        $o = F::append(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], 6);
        $this->assertTrue(is_array($o));
        $this->assertEquals([1,2,3,4,5,6], $o);
    }

    public function testAppendItIdx()
    {
        $o = F::append(F::generatorToIterable(fn() => yield from [10, 20, 30, 40]), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([10, 20, 30, 40, 50], iterator_to_array($o, true));
    }

    public function testAppendItAssoc()
    {
        $o = F::append(F::generatorToIterable(fn() => yield from ["i" => 10, "j" => 20, "k" => 30, "l" => 40]), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([10, 20, 30, 40, 50], iterator_to_array($o, true));
    }

    // appendK

    public function testAppendKArrayIdx()
    {
        $o = F::appendK([1,2,3,4,5], 6, 5);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([1,2,3,4,5,6], iterator_to_array($o, true));
    }

    public function testAppendKArrayAssoc()
    {
        $o = F::appendK(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], 6, "f");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5, "f" => 6], iterator_to_array($o, true));
    }

    public function testAppendKItIdx()
    {
        $o = F::appendK($this->toGen([10, 20, 30, 40]), 50, 4);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([10, 20, 30, 40, 50], iterator_to_array($o, true));
    }

    public function testAppendKItAssoc()
    {
        $o = F::appendK($this->toGen(["i" => 10, "j" => 20, "k" => 30, "l" => 40]), 50, "m");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 30, "l" => 40, "m" => 50], iterator_to_array($o, true));
    }

    public function testAppendKRepeatedKeys()
    {
        $o = F::appendK(["a" => 1, "b" => 2, "c" => 3], 4, "b");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "b" => 4], iterator_to_array($o, true));
    }

    private function toGen($arr)
    {
        yield from $arr;
    }
}