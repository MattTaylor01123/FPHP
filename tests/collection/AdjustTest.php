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
use Traversable;

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
        $this->assertTrue($o3 instanceof stdClass);
        $this->assertEquals((object)["f" => 2, "g" => 4, "h" => 6], $v3);
        $this->assertEquals((object)["f" => 2, "g" => 5, "h" => 6], $o3);
    }

    public function testAdjustItAssoc()
    {
        $count = 0;
        $fn = function($x) use(&$count) {
            $count = $count + 1;
            return $x + 1;
        };

        $v4 = $this->getItAssoc();
        $o4 = F::adjust("k", $fn, $v4);

        $this->assertTrue($o4 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        // check for laziness during the run
        $results = [];
        foreach($o4 as $k => $v)
        {
            $results[$k] = $v;
            $this->assertEquals(in_array($k, ["k", "l"]) ? 1 : 0, $count);
        }

        // check overall result
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 31, "l" => 40], $results);

        // repeat the check to run iterator_to_array again, to make sure
        // of generator reuse
        $this->assertEquals(["i" => 10, "j" => 20, "k" => 31, "l" => 40], iterator_to_array($o4));
        $this->assertEquals(2, $count);
    }

    public function testAdjustCusType()
    {
        $v = new TestType();
        $v->a = 1;
        $v->b = "h";
        $o = F::adjust("a", F::inc(), $v);
        $this->assertTrue($o instanceof TestType);
        $exp = new TestType();
        $exp->a = 2;
        $exp->b = "h";
        $this->assertEquals($exp, $o);
    }
}