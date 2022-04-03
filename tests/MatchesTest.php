<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class MatchesTest extends TestCase
{
    public function testIt()
    {
        function matches(iterable $criteria)
        {
            $conditions = F::map(function($crit) {
                list($key, $func) = $crit;
                $ops = new class($key) {
                    public function __construct(
                        private $a
                    ) {}

                    public function gt($b) {
                        return "{$this->a} > $b";
                    }

                    public function eq($b) {
                        return "{$this->a} = $b";
                    }

                    public function neq($b) {
                        return "{$this->a} != $b";
                    }
                };
                $cond = $func($ops);
                return "($cond)";
            }, $criteria);

            return F::joinUp(" && ", $conditions);
        }

        $res = matches([
            ["age", F::gt(7)],
            ["gender", F::neq("female")],
            ["name", F::eq("Kelly")]
        ]);

        //error_log($res);
        $this->assertTrue(false);
    }
}