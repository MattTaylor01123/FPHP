<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class DissocTest extends TestCase
{
    public function testDissocObj()
    {
        $obj = new \stdClass();
        $obj->firstName  = "Steve";
        $obj->lastName = "Smith";
        $obj->age = 17;

        $res = F::dissoc($obj, "firstName");

        $this->assertTrue($res instanceof \stdClass);
        $this->assertEquals((object)["lastName" => "Smith", "age" => 17], $res);

        $res2 = F::dissoc($res, "age");

        $this->assertTrue($res2 instanceof \stdClass);
        $this->assertEquals((object)["lastName" => "Smith"], $res2);
    }
}