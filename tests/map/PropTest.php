<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\map;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class PropTest extends TestCase
{
    use TestUtils;

    public function testPropObject()
    {
        $this->assertSame(F::prop("f", $this->getObj()), 2);
        $this->assertSame(F::prop("i", $this->getObj()), null);
    }

    public function testPropArray()
    {
        $this->assertSame(F::prop("a", $this->getAssocArray()), 1);
        $this->assertSame(F::prop("f", $this->getAssocArray()), null);
    }

    public function testPropArray2()
    {
        $this->assertSame(F::prop(0, $this->getIndexedArray()), 1);
        $this->assertSame(F::prop(6, $this->getIndexedArray()), null);
    }
}