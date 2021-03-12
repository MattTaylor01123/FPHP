<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use Traversable;

final class ConcatTest extends TestCase
{
    public function toGen($vals)
    {
        yield from $vals;
    }

    public function cases()
    {
        return [
            [
                [0,1,2],
                [3,4],
                F::isArray(),
                [0,1,2,3,4]
            ],[
                ["a" => 1, "b" => 2],
                ["a" => 3, "c" => 4],
                F::isArray(),
                [1,2,3,4]
            ],[
                "hello ",
                "world",
                F::isString(),
                "hello world"
            ],[
                $this->toGen([1,2]),
                $this->toGen([3,4]),
                F::isTraversable(),
                $this->toGen([1,2,3,4]),
                false
            ],[
                $this->toGen(["a" => 1, "b" => 2]),
                $this->toGen(["a" => 3, "c" => 4]),
                F::isTraversable(),
                $this->toGen(["a" => 3, "b" => 2, "c" => 4]),
                true
            ]
        ];
    }

    /**
     * @dataProvider cases
     */
    public function testConcat($v1, $v2, callable $expTypeCheck, $exp, $isAssoc = false)
    {
        $act = F::concat($v1, $v2);
        $this->assertTrue($expTypeCheck($act));

        $this->assertEquals(
            // use false here as merge ignores keys (e.g. uses append), so can't
            // force keys as all keys will be the same (0)
            $exp instanceof Traversable ? iterator_to_array($exp, $isAssoc) : $exp,
            $act instanceof Traversable ? iterator_to_array($act, $isAssoc) : $act
        );
    }
}