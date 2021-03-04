<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class InToTest extends TestCase
{
    use TestUtils;

    function testInTo()
    {
        $fn = F::pipe(
            F::mapT(fn($v) => $v + 1),
            F::filterT(fn($v) => $v % 2)
        );

        $out1 = F::inTo(new \stdClass(), $fn, $this->getAssocArray());
        $out2 = F::inTo([], $fn, $this->getAssocArray());
        
        $this->assertEquals((object)["a" => 2, "c" => 4, "e" => 6], $out1);
        $this->assertEquals(["a" => 2, "c" => 4, "e" => 6], $out2);
    }
}