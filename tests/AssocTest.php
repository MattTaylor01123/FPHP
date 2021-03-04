<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class AssocTest extends TestCase
{
    public function testAssoc()
    {
        $obj = new \stdClass();
        $obj->a = 5;

        $obj2 = F::assoc($obj, 6, "b");

        $this->assertNotSame($obj, $obj2);
        $this->assertEquals((object)["a" => 5], $obj);
        $this->assertEquals((object)["a" => 5, "b" => 6], $obj2);
    }
}