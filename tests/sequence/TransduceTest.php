<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use src\utilities\TransformedTraversable;

final class TransduceTest extends TestCase
{
    public function testArray()
    {
        $in = [1,2,3,4];
        $out = F::transduce(F::filterT(fn($v) => $v % 2 === 0), fn($acc, $v) => F::append($acc, $v), [], $in);
        $this->assertTrue(is_array($out));
        $this->assertEquals([2,4], $out);
        
        $fnEmptyIterator = fn() => yield from [];        
        $out2 = F::transduce(F::filterT(fn($v) => $v % 2 === 0), fn($acc, $v) => F::append($acc, $v), $fnEmptyIterator(), $in);
        $this->assertTrue(!is_array($out2) && ($out2 instanceof TransformedTraversable));

        $this->assertEquals([2,4], iterator_to_array($out2));
    }
}
