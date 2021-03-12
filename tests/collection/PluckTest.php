<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;
use Traversable;

final class PluckTest extends TestCase
{
    use TestUtils;

    function testPluckIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::pluck("name", $v);
        $this->assertSame($out1, ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testPluckIt()
    {
        $v = $this->getPersonsDataIt();
        $out1 = F::pluck("name", $v);
        $this->assertTrue(is_object($out1));
        $this->assertTrue($out1 instanceof Traversable);
        $this->assertSame(iterator_to_array($out1, false), ["Matt", "Sheila", "Steve", "Cecilia", "Verity"]);
    }

    function testPluckOverride()
    {
        $collection = $this->buildCollectionMock("pluck", "name", ["hello", "world"]);
        $out2 = F::pluck("name", $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }
}