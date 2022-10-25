<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class GroupMapByTest extends TestCase
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

        $res = F::groupMapBy(fn($v) => substr($v, 0, 1), fn($v) => strtoupper($v), $names);

        $this->assertEquals([
            "A" => ["ANNA"],
            "B" => ["BARBARA", "BILL"],
            "J" => ["JOHN"],
            "K" => ["KAREN", "KIM"],
            "S" => ["STEVE"]
        ], $res);
    }
}