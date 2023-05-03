<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;
use tests\TestUtils;

final class HasPropTest extends TestCase
{
    use TestUtils;

    public function testHasPropObject()
    {
        $this->assertTrue(F::hasProp("f", $this->getObj()));
        $this->assertFalse(F::hasProp("i", $this->getObj()));
    }

    public function testHasPropArray()
    {
        $this->assertTrue(F::hasProp("a", $this->getAssocArray()));
        $this->assertFalse(F::hasProp("f", $this->getAssocArray()));
    }

    public function testHasPropCustType()
    {
        $v = new TestType();
        $v->a = 1;

        $this->assertTrue(F::hasProp("a", $v));
        $this->assertFalse(F::hasProp("c", $v));
    }
}