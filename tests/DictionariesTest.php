<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use IteratorAggregate;
use RamdaPHP\Core as C;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DictionariesTest extends TestCase
{
    use IterableDefs;

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

    /**
     * @dataProvider buildObject
     */
    public function testPropObject(object $obj)
    {
        $this->assertSame(C::prop("firstName", $obj), "Matt");
        $this->assertSame(C::prop("middleName", $obj), null);
        
        $fn = C::prop("firstName");
        $this->assertSame($fn($obj), "Matt");
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropArray(array $arr)
    {
        $this->assertSame(C::prop("firstName", $arr), "Matt");
        $this->assertSame(C::prop("middleName", $arr), null);
        
        $fn = C::prop("firstName");
        $this->assertSame($fn($arr), "Matt");
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testPropsObject(object $obj)
    {
        $o1 = C::props(["firstName", "middleName","lastName"], $obj);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropsArray(array $arr)
    {
        $o1 = C::props(["firstName", "middleName","lastName"], $arr);
        $e1 = ["Matt", null, "Taylor"];
        $this->assertEquals($o1, $e1);
    }
    
    /**
     * @dataProvider buildObject
     */
    public function testHasPropObject(object $obj)
    {
        $this->assertTrue(C::hasProp("firstName", $obj));
        $this->assertFalse(C::hasProp("middleName", $obj));
        
        $fn = C::hasProp("firstName");
        $this->assertTrue($fn($obj));
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testHasPropArray(array $arr)
    {
        $this->assertTrue(C::hasProp("firstName", $arr));
        $this->assertFalse(C::hasProp("middleName", $arr));
        
        $fn = C::hasProp("firstName");
        $this->assertTrue($fn($arr));
    }


    function testKeysAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = C::keys($v);
        $this->assertSame($out1, ["a", "b", "c", "d", "e"]);
    }

    function testKeysObj()
    {
        $v = $this->getObj();
        $out1 = C::keys($v);
        $this->assertSame($out1, ["f", "g", "h"]);
    }

    function testKeysOverride()
    {
        $collection = $this->buildCollectionMock2("keys", null, ["hello", "world"]);
        $out2 = C::keys($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testKeysItIdx()
    {
        $v = $this->getItIdx();
        $out = C::keys($v);
        $this->assertEquals(iterator_to_array($out, false), [0,1,2,3]);
    }

    function testKeysItAssoc()
    {
        $v = $this->getItAssoc();
        $out = C::keys($v);
        $this->assertSame(iterator_to_array($out, false), ["i","j","k","l"]);
    }

    /**
     * @dataProvider buildObject
     */
    public function testPickObject(object $obj)
    {
        $o1 = C::pick(["firstName", "middleName","lastName"], $obj);
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
        $o1 = C::pick(["firstName", "middleName","lastName"], $arr);
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
        $o1 = C::pick(["firstName", "middleName", "lastName"], $itAssoc);
        $e1 = array();
        $e1["firstName"] = "Matt";
        $e1["lastName"] = "Taylor";
        $this->assertEquals(iterator_to_array($o1, true), $e1);
    }

    function testValuesAssoc()
    {
        $v = $this->getAssocArray();
        $out1 = C::values($v);
        $this->assertSame($out1, [1,2,3,4,5]);
    }

    function testValuesObj()
    {
        $v = $this->getObj();
        $out1 = C::values($v);
        $this->assertSame($out1, [2,4,6]);
    }

    function testValuesOverride()
    {
        $collection = $this->buildCollectionMock2("values", null, ["hello", "world"]);
        $out2 = C::values($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testValuesItIdx()
    {
        $v = $this->getItIdx();
        $out = C::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }

    function testValuesItAssoc()
    {
        $v = $this->getItAssoc();
        $out = C::values($v);
        $this->assertSame(iterator_to_array($out, false), [10,20,30,40]);
    }
}
