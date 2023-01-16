<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class ConcatTest extends TestCase
{
    public function testConcat()
    {
        $res0 = F::concat();
        $this->assertTrue(F::isArray($res0));
        $this->assertEquals([], $res0);

        $res1 = F::concat([0,1,2]);
        $this->assertTrue(F::isArray($res1));
        $this->assertEquals([0,1,2], $res1);

        $res2 = F::concat([0,1,2], [3,4]);
        $this->assertTrue(F::isArray($res2));
        $this->assertEquals([0,1,2,3,4], $res2);

        $res3 = F::concat([0,1,2], [3,4], [5,6]);
        $this->assertTrue(F::isArray($res3));
        $this->assertEquals([0,1,2,3,4,5,6], $res3);

        $res4 = F::concat(["a" => 0, "b" => 1, "c" => 2], ["d" => 3, "e" => 4]);
        $this->assertTrue(F::isArray($res4));
        $this->assertEquals([0,1,2,3,4], $res4);

        $res5 = F::concat([0,1,2], $this->toGen([3,4]));
        $this->assertTrue(F::isTraversable($res5));
        $this->assertEquals([0,1,2,3,4], iterator_to_array($res5, true));

        $res6 = F::concat($this->toGen([0,1,2]), $this->toGen([3,4]));
        $this->assertTrue(F::isTraversable($res6));
        $this->assertEquals([0,1,2,3,4], iterator_to_array($res6, true));
    }

    public function testConcatK()
    {
        $res0 = F::concatK();
        $this->assertTrue(F::isTraversable($res0));
        $this->assertEquals([], iterator_to_array($res0, true));

        $res1 = F::concatK(["a" => 0, "b" => 1, "c" => 2]);
        $this->assertTrue(F::isTraversable($res1));
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2], iterator_to_array($res1, true));

        $res2 = F::concatK(["a" => 0, "b" => 1, "c" => 2], ["d" => 3, "e" => 4]);
        $this->assertTrue(F::isTraversable($res2));
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4], iterator_to_array($res2, true));

        $res3 = F::concatK(["a" => 0, "b" => 1, "c" => 2], ["d" => 3, "e" => 4], ["f" => 5, "g" => 6]);
        $this->assertTrue(F::isTraversable($res3));
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4, "f" => 5, "g" => 6], iterator_to_array($res3, true));

        $res4 = F::concatK([0,1,2], [3,4]);
        $this->assertTrue(F::isTraversable($res4));
        $this->assertEquals([3,4,2], iterator_to_array($res4, true));

        $res5 = F::concatK(["a" => 0, "b" => 1, "c" => 2], $this->toGen(["d" => 3, "e" => 4]));
        $this->assertTrue(F::isTraversable($res5));
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4], iterator_to_array($res5, true));

        $res6 = F::concatK($this->toGen(["a" => 0, "b" => 1, "c" => 2]), $this->toGen(["d" => 3, "e" => 4]));
        $this->assertTrue(F::isTraversable($res6));
        $this->assertEquals(["a" => 0, "b" => 1, "c" => 2, "d" => 3, "e" => 4], iterator_to_array($res6, true));
    }

    private function toGen($arr)
    {
        yield from $arr;
    }
}