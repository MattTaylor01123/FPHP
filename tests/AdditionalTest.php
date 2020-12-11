<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;

final class AdditionalTest extends TestCase
{
    use IterableDefs;

    function buildCollectionMock2(string $overrideFunction, $in, $out)
    {
        $collection =  $this->getMockBuilder(IteratorAggregate::class)
            ->setMethods(["getIterator", $overrideFunction])
            ->getMock();
        $t = $collection->expects($this->once())
            ->method($overrideFunction);
        if($in !== null)
        {
            $t->with($this->equalTo($in));
        }
        if($out !== null)
        {
            $t->willReturn($out);
        }
        return $collection;
    }

    function testColumnsIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = R::columns(["name", "family"], $v);
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
        $collection = $this->buildCollectionMock2("columns", ["name", "family"], [
            (object)["name" => "Matt", "family" => "Smith"],
            (object)["name" => "Sheila", "family" => "Smith"],
        ]);
        $out2 = R::columns(["name", "family"], $collection);
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