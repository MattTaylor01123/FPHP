<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use FPHP\FPHP as F;
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

    public function buildSeqArray()
    {
        $arr = array();
        $arr[] = "Matt";
        $arr[] = "Taylor";
        $arr[] = 137;
        return [[$arr]];
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testPropObject(object $obj)
    {
        $this->assertSame(F::prop("firstName", $obj), "Matt");
        $this->assertSame(F::prop("middleName", $obj), null);
        
        $fn = F::prop("firstName");
        $this->assertSame($fn($obj), "Matt");
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropArray(array $arr)
    {
        $this->assertSame(F::prop("firstName", $arr), "Matt");
        $this->assertSame(F::prop("middleName", $arr), null);
        
        $fn = F::prop("firstName");
        $this->assertSame($fn($arr), "Matt");
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testPropsObject(object $obj)
    {
        $o1 = F::props(["firstName", "middleName","lastName"], $obj);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropsArray(array $arr)
    {
        $o1 = F::props(["firstName", "middleName","lastName"], $arr);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }

    /**
     * @dataProvider buildSeqArray
     */
    public function testPropArray2(array $arr)
    {
        $this->assertSame(F::prop(0, $arr), "Matt");
        $this->assertSame(F::prop(1, $arr), "Taylor");
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testHasPropObject(object $obj)
    {
        $this->assertTrue(F::hasProp("firstName", $obj));
        $this->assertFalse(F::hasProp("middleName", $obj));
        
        $fn = F::hasProp("firstName");
        $this->assertTrue($fn($obj));
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testHasPropArray(array $arr)
    {
        $this->assertTrue(F::hasProp("firstName", $arr));
        $this->assertFalse(F::hasProp("middleName", $arr));
        
        $fn = F::hasProp("firstName");
        $this->assertTrue($fn($arr));
    }


    function testKeysAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = F::keys($v);
        $this->assertSame($out1, ["a", "b", "c", "d", "e"]);
    }

    function testKeysObj()
    {
        $v = $this->getObj();
        $out1 = F::keys($v);
        $this->assertSame($out1, ["f", "g", "h"]);
    }

    function testKeysOverride()
    {
        $collection = $this->buildCollectionMock("keys", null, ["hello", "world"]);
        $out2 = F::keys($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testKeysItIdx()
    {
        $v = $this->getItIdx();
        $out = F::keys($v);
        $this->assertEquals(iterator_to_array($out, false), [0,1,2,3]);
    }

    function testKeysItAssoc()
    {
        $v = $this->getItAssoc();
        $out = F::keys($v);
        $this->assertSame(iterator_to_array($out, false), ["i","j","k","l"]);
    }

    /**
     * @dataProvider buildObject
     */
    public function testPickObject(object $obj)
    {
        $o1 = F::pick(["firstName", "middleName","lastName"], $obj);
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
        $o1 = F::pick(["firstName", "middleName","lastName"], $arr);
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
        $o1 = F::pick(["firstName", "middleName", "lastName"], $itAssoc);
        $e1 = array();
        $e1["firstName"] = "Matt";
        $e1["lastName"] = "Taylor";
        $this->assertEquals(iterator_to_array($o1, true), $e1);
    }

    function testValuesAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = F::values($v);
        $this->assertSame($out1, [1,2,3,4,5]);
    }

    function testValuesObj()
    {
        $v = $this->getObj();
        $out1 = F::values($v);
        $this->assertSame($out1, [2,4,6]);
    }

    function testValuesOverride()
    {
        $collection = $this->buildCollectionMock("values", null, ["hello", "world"]);
        $out2 = F::values($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testValuesItIdx()
    {
        $v = $this->getItIdx();
        $out = F::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }

    function testValuesItAssoc()
    {
        $v = $this->getItAssoc();
        $out = F::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }
}
