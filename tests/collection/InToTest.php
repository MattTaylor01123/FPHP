<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use stdClass;
use tests\TestType;
use tests\TestUtils;

final class InToTest extends TestCase
{
    use TestUtils;

    function testInTo()
    {
        $fn = F::pipe(
            F::mapT(fn($v) => $v + 1),
            F::filterT(fn($v) => $v % 2)
        );

        $out1 = F::inTo(new stdClass(), $fn, $this->getAssocArray());
        $out2 = F::inTo([], $fn, $this->getAssocArray());
        
        $this->assertEquals((object)["a" => 2, "c" => 4, "e" => 6], $out1);
        $this->assertEquals(["a" => 2, "c" => 4, "e" => 6], $out2);
    }

    public function testIntoCustType()
    {
        $v = new TestType();
        $v->a = 1;
        $v->b = "h";
        $o = F::inTo(new TestType(), F::adjustT("a", F::inc()), $v);

        $exp = new TestType();
        $exp->a = 2;
        $exp->b = "h";

        $this->assertTrue($o instanceof TestType);
        $this->assertEquals($exp, $o);
    }
}