<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use FPHP\FPHP as F;

final class AdditionalTest extends TestCase
{
    use TestUtils;

    function testColumnsIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::columns(["name", "family"], $v);
        $this->assertEquals($out1, [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
            (object)["name" => "Steve", "family" => "Jones"],
            (object)["name" => "Cecilia", "family" => "Jones"],
            (object)["name" => "Verity", "family" => "Smith"]
        ]);
    }

    function testColumnsOverride()
    {
        $collection = $this->buildCollectionMock("columns", ["name", "family"], [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
        ]);
        $out2 = F::columns(["name", "family"], $collection);
        $this->assertEquals($out2, [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
        ]);
    }

    // function testMapTo()
    // {
    //   TODO
    // }
}