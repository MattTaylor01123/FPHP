<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class GroupReduceByTest extends TestCase
{
    public function testArray()
    {
        $names = [
            "Anna",
            "Barbara",
            "John",
            "Karen",
            "Kim",
            "Steve",
            "Bill"
        ];

        $res = F::groupReduceBy(fn($r) => substr($r, 0, 1),
                                fn($acc, $v) => strlen($acc) > 0 ? "$acc, $v" : $v,
                                "",
                                $names);

        $this->assertEquals([
            "A" => "Anna",
            "B" => "Barbara, Bill",
            "J" => "John",
            "K" => "Karen, Kim",
            "S" => "Steve"
        ], $res);   
    }

    public function testGenerator()
    {
        $names = fn() => yield from [
            "Anna",
            "Barbara",
            "John",
            "Karen",
            "Kim",
            "Steve",
            "Bill"
        ];

        function emptyIterable() { yield from []; }

        $res = F::groupReduceBy(fn($r) => substr($r, 0, 1), fn($acc, $v) => F::append($acc, $v), emptyIterable(), $names());

        $this->assertTrue(is_array($res));
        $out = array();
        foreach($res as $k => $v)
        {
            $this->assertTrue(is_iterable($v) && !is_array($v));
            $out[$k] = iterator_to_array($v, false);
        }

        $this->assertEquals([
            "A" => ["Anna"],
            "B" => ["Barbara", "Bill"],
            "J" => ["John"],
            "K" => ["Karen", "Kim"],
            "S" => ["Steve"]
        ], $out);
    }
}