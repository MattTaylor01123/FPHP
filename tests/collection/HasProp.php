<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;
use tests\TestUtils;

final class HasProp extends TestCase
{
    use TestUtils;

    public function testHasPropObject()
    {
        $this->assertTrue(F::hasProp("f", $this->getObj()));
        $this->assertFalse(F::hasProp("i", $this->getObj()));

        $fn = F::hasProp("f");
        $this->assertTrue($fn($this->getObj()));
    }

    public function testHasPropArray()
    {
        $this->assertTrue(F::hasProp("a", $this->getAssocArray()));
        $this->assertFalse(F::hasProp("f", $this->getAssocArray()));

        $fn = F::hasProp("a");
        $this->assertTrue($fn($this->getAssocArray()));
    }

    public function testHasPropCustType()
    {
        $v = new TestType();
        $v->a = 1;

        $this->assertTrue(F::hasProp("a", $v));
        $this->assertFalse(F::hasProp("c", $v));
    }
}