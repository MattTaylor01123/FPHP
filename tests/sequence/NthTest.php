<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class NthTest extends TestCase
{
    public function testArray()
    {
        $in1 = ["a", "b", "c", "d", "e"];
        $this->assertEquals("a", F::nth(0, $in1));
        $this->assertEquals("b", F::nth(1, $in1));
        $this->assertEquals("c", F::nth(2, $in1));
        $this->assertEquals("d", F::nth(3, $in1));
        $this->assertEquals("e", F::nth(4, $in1));
        $this->assertEquals(null, F::nth(5, $in1));

        $this->assertEquals("e", F::nth(-1, $in1));
        $this->assertEquals("d", F::nth(-2, $in1));
        $this->assertEquals("c", F::nth(-3, $in1));
        $this->assertEquals("b", F::nth(-4, $in1));
        $this->assertEquals("a", F::nth(-5, $in1));
        $this->assertEquals(null, F::nth(-6, $in1));
    }

    public function testIterator()
    {
        $in1 = ["a", "b", "c", "d", "e"];
        $this->assertEquals("a", F::nth(0, $this->toGen($in1)));
        $this->assertEquals("b", F::nth(1, $this->toGen($in1)));
        $this->assertEquals("c", F::nth(2, $this->toGen($in1)));
        $this->assertEquals("d", F::nth(3, $this->toGen($in1)));
        $this->assertEquals("e", F::nth(4, $this->toGen($in1)));
        $this->assertEquals(null, F::nth(5, $this->toGen($in1)));

        $this->assertEquals("e", F::nth(-1, $this->toGen($in1)));
        $this->assertEquals("d", F::nth(-2, $this->toGen($in1)));
        $this->assertEquals("c", F::nth(-3, $this->toGen($in1)));
        $this->assertEquals("b", F::nth(-4, $this->toGen($in1)));
        $this->assertEquals("a", F::nth(-5, $this->toGen($in1)));
        $this->assertEquals(null, F::nth(-6, $this->toGen($in1)));
    }
    
    public function testThreadable()
    {
        $in1 = ["a", "b", "c", "d", "e"];
        $fn = F::nth(2);
        
        $this->assertTrue(is_callable($fn));
        $this->assertEquals("c", $fn($in1));
    }

    private function toGen($arr)
    {
        yield from $arr;
    }
}