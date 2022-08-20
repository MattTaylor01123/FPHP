<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\collection;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;
use Traversable;

final class IndexByTest extends TestCase
{
    use TestUtils;

    function testIndexByIdx()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::indexBy(fn($x) => F::prop("gender", $x), $v);
        $this->assertIsArray($out1);
        $this->assertCount(2, $out1);
        $this->assertArrayHasKey("M", $out1);
        $this->assertArrayHasKey("F", $out1);
        $this->assertSame($v[2], $out1["M"]);
        $this->assertSame($v[4], $out1["F"]);
    }

    function testIndexByItAssoc()
    {
        $count = 0;
        $fn = function($v) use(&$count) {
            $count = $count + 1;
            return F::prop("gender", $v);
        };

        $v = $this->getPersonsDataIt();
        $out1 = F::indexBy($fn, $v);

        $this->assertTrue($out1 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        $i = 0;
        $v2 = $this->getPersonsDataIdx();
        foreach($out1 as $k => $val)
        {
            $this->assertEquals($v2[$i], $val);
            $this->assertSame($v2[$i]->gender, $k);
            $i++;
            $this->assertEquals($i, $count);
        }
    }

    function testIndexByOverride()
    {
        $fn = fn($v) => F::prop("family", $v);
        $collection = $this->buildCollectionMock("indexBy", $fn, ["hello", "world"]);
        $out2 = F::indexBy($fn, $collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testIndexByTransducer()
    {
        $v = $this->getPersonsDataIdx();
        $out1 = F::transduce(
            fn($step) => F::indexByT(fn($x) => F::prop("gender", $x), $step),
            fn($acc, $v, $k) => F::assoc($acc, $v, $k),
            [],
            $v
        );
        $this->assertIsArray($out1);
        $this->assertCount(2, $out1);
        $this->assertArrayHasKey("M", $out1);
        $this->assertArrayHasKey("F", $out1);
        $this->assertSame($v[2], $out1["M"]);
        $this->assertSame($v[4], $out1["F"]);
    }
}