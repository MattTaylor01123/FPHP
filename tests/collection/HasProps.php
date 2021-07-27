<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestType;
use tests\TestUtils;

final class HasProps extends TestCase
{
    use TestUtils;

    public function testHasPropsObject()
    {
        $this->assertTrue(F::hasProps(["f", "g", "h"], $this->getObj()));
        $this->assertFalse(F::hasProps(["f", "g", "h", "i"], $this->getObj()));

        $fn = F::hasProps(["f", "g", "h"]);
        $this->assertTrue($fn($this->getObj()));
    }

    public function testHasPropsArray()
    {
        $this->assertTrue(F::hasProps(["c", "d", "e"], $this->getAssocArray()));
        $this->assertFalse(F::hasProps(["c", "d", "e", "f"], $this->getAssocArray()));

        $fn = F::hasProps(["c", "d", "e"]);
        $this->assertTrue($fn($this->getAssocArray()));
    }

    public function testHasPropsCustType()
    {
        $v = new TestType();
        $v->a = 1;
        $v->b = 2;
        $v->c = 3;

        $this->assertTrue(F::hasProps(["a", "b", "c"], $v));
        $this->assertFalse(F::hasProps(["a", "b", "c", "d"], $v));
    }
}