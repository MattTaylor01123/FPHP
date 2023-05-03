<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class PropEqTest extends TestCase
{
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
    
    public function buildSeqArray()
    {
        $arr = array();
        $arr[] = "Matt";
        $arr[] = "Taylor";
        $arr[] = 137;
        return [[$arr]];
    }
    
    /**
     * @dataProvider buildArray
     */
    public function testPropEqArray(array $arr)
    {
        $this->assertTrue(F::propEq("firstName", "Matt", $arr));
        $this->assertFalse(F::propEq("lastName", "Matt", $arr));   
    }
    
    /**
     * @dataProvider buildSeqArray
     */
    public function testPropEqArray2(array $arr)
    {
        $this->assertTrue(F::propEq(0, "Matt", $arr));
        $this->assertFalse(F::propEq(1, "Matt", $arr));
    }
}
