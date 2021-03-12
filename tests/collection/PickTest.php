<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use stdClass;
use tests\TestUtils;

final class PickTest extends TestCase
{
    use TestUtils;

    public function testPickObject()
    {
        $o1 = F::pick(["f", "g","i"], $this->getObj());
        $e1 = new stdClass();
        $e1->f = 2;
        $e1->g = 4;
        $this->assertEquals($o1, $e1);
    }

    public function testPickArray()
    {
        $o1 = F::pick(["a", "b","f"], $this->getAssocArray());
        $e1 = array();
        $e1["a"] = 1;
        $e1["b"] = 2;
        $this->assertEquals($o1, $e1);
    }

    public function testPickIterable()
    {
        $o1 = F::pick(["i", "j", "m"], $this->getItAssoc());
        $e1 = array();
        $e1["i"] = 10;
        $e1["j"] = 20;
        $this->assertEquals(iterator_to_array($o1, true), $e1);
    }
}