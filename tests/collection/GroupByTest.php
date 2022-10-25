<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class GroupByTest extends TestCase
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

        $res = F::groupBy(fn($v) => substr($v, 0, 1), $names);

        $this->assertEquals([
            "A" => ["Anna"],
            "B" => ["Barbara", "Bill"],
            "J" => ["John"],
            "K" => ["Karen", "Kim"],
            "S" => ["Steve"]
        ], $res);
    }
}