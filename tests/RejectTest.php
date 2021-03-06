<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class RejectTest extends TestCase
{
    function testReject()
    {
        $arr = [1,2,3,4,5,6];
        $fnEven = fn ($v) => $v % 2 === 0;
        $res = F::reject($fnEven, $arr);

        $this->assertSame($res, [1,3,5]);
    }
}