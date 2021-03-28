<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class IterableToArrayTest extends TestCase
{
    use TestUtils;

    function dpTestAll()
    {
        return [
            [$this->getItIdx(), [10, 20, 30, 40]],
            [$this->getItAssoc(), ["i" => 10, "j" => 20, "k" => 30, "l" => 40]]
        ];
    }

    /**
     * @dataProvider dpTestAll
     */
    function testAll($in, $exp)
    {
        $act = F::iterableToArray($in);
        $this->assertEquals($exp, $act);
    }
}