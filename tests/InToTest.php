<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class InToTest extends TestCase
{
    use TestUtils;

    function testInTo()
    {
        $fn = R::pipe(
            R::mapT(fn($v) => $v + 1),
            R::filterT(fn($v) => $v % 2)
        );

        $out1 = R::inTo(new \stdClass(), $fn, $this->getAssocArray());
        $out2 = R::inTo([], $fn, $this->getAssocArray());
        
        $this->assertEquals((object)["a" => 2, "c" => 4, "e" => 6], $out1);
        $this->assertEquals(["a" => 2, "c" => 4, "e" => 6], $out2);
    }
}