<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\RamdaPHP as R;
use PHPUnit\Framework\TestCase;

final class PickAllTest extends TestCase
{
    public function values()
    {
        return [
            [(object)["a" => 1, "b" => 2, "c" => 3], (object)["a" => 1, "b" => 2]],
            [["a" => 1, "b" => 2, "c" => 3], ["a" => 1, "b" => 2]]
        ];
    }

    /**
     * @dataProvider values
     */
    public function testPickAll($input, $expected)
    {
        $output = R::pickAll(["a", "b"], $input);
        $this->assertEquals($output, $expected);
    }
}