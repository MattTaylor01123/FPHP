<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class PathTest extends TestCase
{
    public function makeTestCases()
    {
        $data = (object)[
            "firstName" => "Steve",
            "lastName" => "Smith",
            "age" => 17,
            "jobs" => [
                (object)[
                    "company" => "Chinsey Tea Shop",
                    "role" => "Waiter",
                    "address" => (object)[
                        "line1" => "16, The Mall",
                        "city" => "Makebelieve",
                        "postcode" => "MK1 2PQ"
                    ]
                ],
                (object)[
                    "company" => "Yes! Cinema",
                    "role" => "Customer Service Assistant",
                    "address" => (object)[
                        "line1" => "43, The Mall",
                        "city" => "Makebelieve",
                        "postcode" => "MK1 2PQ"
                    ]
                ]
            ]
        ];

        return [
            [$data, ["firstName"], "Steve"],
            [$data, ["jobs", 0, "company"], "Chinsey Tea Shop"],
            [$data, ["jobs", 1, "address", "line1"], "43, The Mall"],
            [$data, [], $data],
            [$data, ["gender"], null],
            [$data, ["jobs", 2, "company"], null]
        ];
    }

    /**
     * @dataProvider makeTestCases
     */
    public function testPath($data, $path, $exp)
    {
        $act = F::path($path, $data);
        $this->assertEquals($exp, $act);
    }

    public function testAssocPath()
    {
        $data = (object)[
            "firstName" => "Steve",
            "lastName" => "Smith",
            "age" => 17,
            "jobs" => [
                (object)[
                    "company" => "Chinsey Tea Shop",
                    "role" => "Waiter",
                    "address" => (object)[
                        "line1" => "16, The Mall",
                        "city" => "Makebelieve",
                        "postcode" => "MK1 2PQ"
                    ]
                ],
                (object)[
                    "company" => "Yes! Cinema",
                    "role" => "Customer Service Assistant",
                    "address" => (object)[
                        "line1" => "43, The Mall",
                        "city" => "Makebelieve",
                        "postcode" => "MK1 2PQ"
                    ]
                ]
            ]
        ];

        $out = F::assocPath(["jobs", 1, "uniform"], "provided", $data);

        // make sure that the previous arrays / objects have not been modified
        $this->assertFalse(isset($data->jobs[1]->uniform));
        $this->assertFalse($data === $out);
        $this->assertFalse($data->jobs === $out->jobs);
        $this->assertFalse($data->jobs[1] === $out->jobs[1]);

        // make sure that out has been modified
        $this->assertTrue(isset($out->jobs[1]->uniform));
        $this->assertEquals("provided", $out->jobs[1]->uniform);

        // make sure no other keys have been added / changed / deleted
        $this->assertFalse($data == $out);
        unset($out->jobs[1]->uniform);
        $this->assertTrue($data == $out);
    }
}