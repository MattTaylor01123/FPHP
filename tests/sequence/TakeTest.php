<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class TakeTest extends TestCase
{
    use TestUtils;

    public function cases()
    {
        return [
            [$this->getIndexedArray(), 1, [1]],
            [$this->getIndexedArray(), 2, [1,2]],
            [$this->getIndexedArray(), 3, [1,2,3]],
            [$this->getAssocArray(), 3, ["a" => 1, "b" => 2, "c" => 3]],
            [$this->getItIdx(), 3, [10, 20, 30]],
            [$this->getItAssoc(), 3, ["i" => 10, "j" => 20, "k" => 30]]
        ];
    }

    /**
     * @dataProvider cases
     */
    public function testGeneral($in, $count, $exp)
    {
        $act = F::take($count, $in);

        if(is_array($in))
        {
            $this->assertSame($exp, $act);
        }
        else
        {
            $useKeys = array_keys($exp)[0] !== 0;
            $this->assertEquals($exp, iterator_to_array($act, $useKeys));
        }
    }

    public function testLazyness()
    {
        $count = 0;
        $fn = function($v) use(&$count) {
            $count = $count + 1;
            return $v > 10;
        };

        $out = F::pipex($this->getItIdx(),
            fn($coll) => F::filter($fn, $coll),
            fn($coll) => F::take(1, $coll)
        );

        // no iteration has occurred yet
        $this->assertEquals(0, $count);

        $vals = array();
        $i = 0;
        foreach($out as $v)
        {
            $i = $i + 1;
            $this->assertEquals($i + 1, $count);
            $vals[] = $v;
        }

        $this->assertEquals(2, $count);
        $this->assertEquals([20], $vals);
    }
}