<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class GroupByTest extends TestCase
{
    public function testGroupBy()
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
        
        $fn = F::groupBy(fn($v) => substr($v, 0, 1));
        $this->assertTrue(is_callable($fn));
        
        $res2 = $fn($names);
        $this->assertEquals([
            "A" => ["Anna"],
            "B" => ["Barbara", "Bill"],
            "J" => ["John"],
            "K" => ["Karen", "Kim"],
            "S" => ["Steve"]
        ], $res2);
    }

    public function testGroupMapBy()
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
        
        $fn = F::groupMapBy(fn($v) => substr($v, 0, 1), fn($v) => strtoupper($v));
        $this->assertTrue(is_callable($fn));
        
        $res2 = $fn($names);
        $this->assertEquals([
            "A" => ["ANNA"],
            "B" => ["BARBARA", "BILL"],
            "J" => ["JOHN"],
            "K" => ["KAREN", "KIM"],
            "S" => ["STEVE"]
        ], $res2);
    }

    public function testGroupReduceBy()
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
        
        $res = F::groupReduceBy(fn($v) => substr($v, 0, 1), fn($a, $v) => $a . $v, "", $names);
        
        $this->assertEquals([
            "A" => "Anna",
            "B" => "BarbaraBill",
            "J" => "John",
            "K" => "KarenKim",
            "S" => "Steve"
        ], $res);
        
        $fn = F::groupReduceBy(fn($v) => substr($v, 0, 1), fn($a, $v) => $a . $v, "");
        $this->assertTrue(is_callable($fn));
        
        $res2 = $fn($names);
        $this->assertEquals([
            "A" => "Anna",
            "B" => "BarbaraBill",
            "J" => "John",
            "K" => "KarenKim",
            "S" => "Steve"
        ], $res2);
    }
}