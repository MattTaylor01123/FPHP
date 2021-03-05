<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class ValuesTest extends TestCase
{
    use TestUtils;

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