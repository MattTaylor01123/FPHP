<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class InToTest extends TestCase
{
    function testInTo()
    {
        $fn = F::pipe(
            F::mapT(fn($v) => $v + 1),
            F::filterT(fn($v) => $v % 2)
        );

        $out1 = F::inToK([], $fn, [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]);
        $this->assertEquals(["a" => 2, "c" => 4, "e" => 6], iterator_to_array($out1));
        
        $out2 = F::inTo([], $fn, [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ]);
        $this->assertEquals([2, 4, 6], $out2);
    }
}