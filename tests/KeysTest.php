<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class KeysTest extends TestCase
{
    use TestUtils;

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
}