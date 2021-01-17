<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class AssocTest extends TestCase
{
    public function testAssoc()
    {
        $obj = new \stdClass();
        $obj->a = 5;

        $obj2 = R::assoc($obj, 6, "b");

        $this->assertNotSame($obj, $obj2);
        $this->assertEquals((object)["a" => 5], $obj);
        $this->assertEquals((object)["a" => 5, "b" => 6], $obj2);
    }
}