<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

class InnerJoinTest extends TestCase
{
    public function testArray()
    {
        $seq1 = [
            (object)["userId" => 1, "first" => "Henry", "last" => "Smith"],
            (object)["userId" => 2, "first" => "Sarah", "last" => "Stone"],
            (object)["userId" => 3, "first" => "Jeremy", "last" => "Bates"]
        ];
        
        $seq2 = [
            (object)["accountId" => 11, "userId" => 1, "email" => "henry.smith@aol.com"],
            (object)["accountId" => 12, "userId" => 3, "email" => "jeremy.bates@hotmail.com"],
            (object)["accountId" => 13, "userId" => 5, "email" => "elliot.clark@gmail.com"]
        ];
        
        $out1 = F::innerJoin(fn($v1, $v2) => $v1->userId === $v2->userId, fn($a, $b) => F::mergeLeft($a, $b), $seq1, $seq2);
        $this->assertEquals([
            (object)["userId" => 1, "first" => "Henry", "last" => "Smith", "accountId" => 11, "email" => "henry.smith@aol.com"],
            (object)["userId" => 3, "first" => "Jeremy", "last" => "Bates", "accountId" => 12, "email" => "jeremy.bates@hotmail.com"]
        ], $out1);
    }
}
