<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class AdditionalTest extends TestCase
{
    use TestUtils;

    function testProjectIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::project(["name", "family"], $v);
        $this->assertEquals($out1, [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
            (object)["name" => "Steve", "family" => "Jones"],
            (object)["name" => "Cecilia", "family" => "Jones"],
            (object)["name" => "Verity", "family" => "Smith"]
        ]);
    }

    function testProjectOverride()
    {
        $collection = $this->buildCollectionMock("project", ["name", "family"], [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
        ]);
        $out2 = F::project(["name", "family"], $collection);
        $this->assertEquals($out2, [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
        ]);
    }
}