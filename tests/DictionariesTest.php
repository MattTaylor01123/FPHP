<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use RamdaPHP\RamdaPHP as R;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DictionariesTest extends TestCase
{
    use TestUtils;

    public function buildObject()
    {
        $obj = new stdClass();
        $obj->firstName = "Matt";
        $obj->lastName = "Taylor";
        $obj->age = 137;
        $obj->books = [
            "The Book of Dust",
            "The Colour of Magic"
        ];
        return [[$obj]];
    }
    
    public function buildArray()
    {
        $arr = array();
        $arr["firstName"] = "Matt";
        $arr["lastName"] = "Taylor";
        $arr["age"] = 137;
        $arr["books"] = [
            "The Book of Dust",
            "The Colour of Magic"
        ];
        return [[$arr]];
    }

    public function buildItAssoc()
    {
        return function() {
            yield "age" => 75;
            yield "city" => "London";
            yield "lastName" => "Taylor";
            yield "firstName" => "Matt";
        };
    }

    /**
     * @dataProvider buildObject
     */
    public function testPropObject(object $obj)
    {
        $this->assertSame(R::prop("firstName", $obj), "Matt");
        $this->assertSame(R::prop("middleName", $obj), null);
        
        $fn = R::prop("firstName");
        $this->assertSame($fn($obj), "Matt");
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropArray(array $arr)
    {
        $this->assertSame(R::prop("firstName", $arr), "Matt");
        $this->assertSame(R::prop("middleName", $arr), null);
        
        $fn = R::prop("firstName");
        $this->assertSame($fn($arr), "Matt");
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testPropsObject(object $obj)
    {
        $o1 = R::props(["firstName", "middleName","lastName"], $obj);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropsArray(array $arr)
    {
        $o1 = R::props(["firstName", "middleName","lastName"], $arr);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testHasPropObject(object $obj)
    {
        $this->assertTrue(R::hasProp("firstName", $obj));
        $this->assertFalse(R::hasProp("middleName", $obj));
        
        $fn = R::hasProp("firstName");
        $this->assertTrue($fn($obj));
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testHasPropArray(array $arr)
    {
        $this->assertTrue(R::hasProp("firstName", $arr));
        $this->assertFalse(R::hasProp("middleName", $arr));
        
        $fn = R::hasProp("firstName");
        $this->assertTrue($fn($arr));
    }


    function testKeysAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = R::keys($v);
        $this->assertSame($out1, ["a", "b", "c", "d", "e"]);
    }

    function testKeysObj()
    {
        $v = $this->getObj();
        $out1 = R::keys($v);
        $this->assertSame($out1, ["f", "g", "h"]);
    }

    function testKeysOverride()
    {
        $collection = $this->buildCollectionMock("keys", null, ["hello", "world"]);
        $out2 = R::keys($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testKeysItIdx()
    {
        $v = $this->getItIdx();
        $out = R::keys($v);
        $this->assertEquals(iterator_to_array($out, false), [0,1,2,3]);
    }

    function testKeysItAssoc()
    {
        $v = $this->getItAssoc();
        $out = R::keys($v);
        $this->assertSame(iterator_to_array($out, false), ["i","j","k","l"]);
    }

    /**
     * @dataProvider buildObject
     */
    public function testPickObject(object $obj)
    {
        $o1 = R::pick(["firstName", "middleName","lastName"], $obj);
        $e1 = new stdClass();
        $e1->firstName = "Matt";
        $e1->lastName = "Taylor";
        $this->assertEquals($o1, $e1);
    }

    /**
     * @dataProvider buildArray
     */
    public function testPickArray(array $arr)
    {
        $o1 = R::pick(["firstName", "middleName","lastName"], $arr);
        $e1 = array();
        $e1["firstName"] = "Matt";
        $e1["lastName"] = "Taylor";
        $this->assertEquals($o1, $e1);
    }

    /**
     * @dataProvider buildItAssoc
     */
    public function testPickIterable($itAssoc)
    {
        $o1 = R::pick(["firstName", "middleName", "lastName"], $itAssoc);
        $e1 = array();
        $e1["firstName"] = "Matt";
        $e1["lastName"] = "Taylor";
        $this->assertEquals(iterator_to_array($o1, true), $e1);
    }

    function testValuesAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = R::values($v);
        $this->assertSame($out1, [1,2,3,4,5]);
    }

    function testValuesObj()
    {
        $v = $this->getObj();
        $out1 = R::values($v);
        $this->assertSame($out1, [2,4,6]);
    }

    function testValuesOverride()
    {
        $collection = $this->buildCollectionMock("values", null, ["hello", "world"]);
        $out2 = R::values($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testValuesItIdx()
    {
        $v = $this->getItIdx();
        $out = R::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }

    function testValuesItAssoc()
    {
        $v = $this->getItAssoc();
        $out = R::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }
}
