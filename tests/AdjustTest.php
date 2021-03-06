<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class AdjustTest extends TestCase
{
    use TestUtils;

    public function testAdjustArrayIdx()
    {
        $v1 = $this->getIndexedArray();
        $o1 = F::adjust(2, F::inc(), $v1);
        $this->assertEquals([1,2,3,4,5], $v1);
        $this->assertEquals([1,2,4,4,5], $o1);
    }

    public function testAdjustArrayAssoc()
    {
        $v2 = $this->getAssocArray();
        $o2 = F::adjust("c", F::dec(), $v2);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5], $v2);
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 2, "d" => 4, "e" => 5], $o2);
    }

    public function testAdjustStdClass()
    {
        $v3 = $this->getObj();
        $o3 = F::adjust("g", F::inc(), $v3);
        $this->assertTrue($o3 instanceof \stdClass);
        $this->assertEquals((object)["f" => 2, "g" => 4, "h" => 6], $v3);
        $this->assertEquals((object)["f" => 2, "g" => 5, "h" => 6], $o3);
    }

    public function testAdjustItAssoc()
    {
        $v4 = $this->getItAssoc();
        $o4 = F::adjust("k", F::inc(), $v4);
        $this->assertTrue($o4 instanceof \Traversable);
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 31, "l" => 40], iterator_to_array($o4));
    }
}