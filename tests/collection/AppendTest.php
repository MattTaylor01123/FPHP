<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class AppendTest extends TestCase
{
    use TestUtils;

    public function testArrayIdx()
    {
        $o = F::append($this->getIndexedArray(), 6);
        $this->assertTrue(is_array($o));
        $this->assertEquals([1,2,3,4,5,6], $o);
    }

    public function testArrayAssoc()
    {
        $o = F::append($this->getAssocArray(), 7);
        $this->assertTrue(is_array($o));
        $this->assertEquals(["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5, 0 => 7], $o);
    }

    public function testItIdx()
    {
        $o = F::append($this->getItIdx(), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals([10, 20, 30, 40, 50], iterator_to_array($o, false));
    }

    public function testItAssoc()
    {
        $o = F::append($this->getItAssoc(), 50);
        $this->assertTrue($o instanceof \Traversable);
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 30, "l" => 40, 0 => 50], iterator_to_array($o, true));
    }
}