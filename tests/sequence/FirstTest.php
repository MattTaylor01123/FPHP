<?php

/*
 * (c) Symbiotics
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use src\utilities\IterableGenerator;

class FirstTest extends TestCase
{
    public function testFirst()
    {
        $this->assertEquals(1, F::first([1,2,3,4,5]));
        $this->assertEquals("a", F::first(["a", "b", "c"]));
        
        $iter = new IterableGenerator(fn() => yield from [10, 20, 30]);
        $this->assertEquals(10, F::first($iter));
        $out = F::first();
        $this->assertTrue(is_callable($out));
        $this->assertEquals(1, $out([1,2,3]));
    }
}
