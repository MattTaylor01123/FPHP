<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use Traversable;

final class MergeTest extends TestCase
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
                ["a" => 3, "b" => 2, "c" => 4]
            ],[
                (object)["a" => 1, "b" => 2],
                (object)["a" => 3, "c" => 4],
                F::isStdClass(),
                (object)["a" => 3, "b" => 2, "c" => 4]
            ]
        ];
    }

    /**
     * @dataProvider cases
     */
    public function testMerge($v1, $v2, callable $expTypeCheck, $exp)
    {
        $act = F::merge($v1, $v2);
        $this->assertTrue($expTypeCheck($act));
        $this->assertEquals(
            $exp instanceof Traversable ? iterator_to_array($exp) : $exp,
            $act instanceof Traversable ? iterator_to_array($act) : $act
        );
    }
}