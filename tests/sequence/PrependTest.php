<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class PrependTest extends TestCase
{
    // prepend

    public function testPrependArrayIdx()
    {
        $o = F::prepend([1,2,3,4,5], 6);
        $this->assertTrue(is_array($o));
        $this->assertEquals([6,1,2,3,4,5], $o);
    }

    public function testPrependArrayAssoc()
    {
        $o = F::prepend(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], 6);
        $this->assertTrue(is_array($o));
        $this->assertEquals([6,1,2,3,4,5], $o);
    }

    public function testPrependItIdx()
    {
        $o = F::prepend(F::generatorToIterable(fn() => yield from [10, 20, 30, 40]), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([50, 10, 20, 30, 40], iterator_to_array($o, true));
    }

    public function testPrependItAssoc()
    {
        $o = F::prepend(F::generatorToIterable(fn() => yield from ["i" => 10, "j" => 20, "k" => 30, "l" => 40]), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([50, 10, 20, 30, 40], iterator_to_array($o, true));
    }
    
    public function testTransducePrepend()
    {
        // the ...v is to make v optional, for when the step function gets called with only one argument
        $out = F::transduce(F::mapT(fn($x) => $x * 2), fn($acc, ...$v) => F::prepend($acc, ...$v), $this->toGen([]), $this->toGen([1,2,3]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals([6,4,2], iterator_to_array($out, false));
    }

    // prependK

    public function testPrependKArrayAssoc()
    {
        $o = F::prependK(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], 6, "f");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["f" => 6, "a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], iterator_to_array($o, true));
    }

    public function testPrependKItAssoc()
    {
        $o = F::prependK($this->toGen(["i" => 10, "j" => 20, "k" => 30, "l" => 40]), 50, "m");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["m" => 50, "i" => 10, "j" => 20, "k" => 30, "l" => 40], iterator_to_array($o, true));
    }

    public function testPrependKRepeatedKeys()
    {
        $o = F::prependK(["a" => 1, "b" => 2, "c" => 3], 4, "b");
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["b" => 4, "a" => 1, "b" => 2, "c" => 3], iterator_to_array($o, true));
    }

    public function testTransducePrependK()
    {
        // the ...v is to make v optional, for when the step function gets called with only one argument
        $out = F::transduce(F::mapT(fn($x, $k) => "{$x}{$k}"), fn($acc, ...$v) => F::prependK($acc, ...$v), $this->toGen([]), $this->toGen(["a" => 1, "b" => 2, "c" => 3]));
        $this->assertTrue($out instanceof \Traversable);
        $this->assertEquals(["3c", "2b", "1a"], iterator_to_array($out, false));
    }
    
    private function toGen($arr)
    {
        yield from $arr;
    }
}