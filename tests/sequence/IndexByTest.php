<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;
use Traversable;

final class IndexByTest extends TestCase
{
    use TestUtils;

    function testIndexByIdx()
    {
        $in = [
            (object)["gender" => "M", "name" => "Matt", "family" => "Smith"],
            (object)["gender" => "F", "name" => "Sheila", "family" => "Smith"],
            (object)["gender" => "M", "name" => "Steve", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Cecilia", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Verity", "family" => "Smith"]
        ];
        
        $out1 = F::indexBy(fn($x) => F::prop("gender", $x), $in);       
        $this->assertTrue($out1 instanceof Traversable);
        $i = 0;
        foreach($out1 as $k => $v)
        {
            $this->assertEquals($in[$i]->gender, $k);
            $this->assertEquals($in[$i], $v);
            $i++;
        }
    }

    function testIndexByItAssoc()
    {
        $count = 0;
        $fn = function($v) use(&$count) {
            $count = $count + 1;
            return F::prop("gender", $v);
        };

        $v = new \src\utilities\IterableGenerator(fn() => yield from [
            (object)["gender" => "M", "name" => "Matt", "family" => "Smith"],
            (object)["gender" => "F", "name" => "Sheila", "family" => "Smith"],
            (object)["gender" => "M", "name" => "Steve", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Cecilia", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Verity", "family" => "Smith"]
        ]);
        $out1 = F::indexBy($fn, $v);

        $this->assertTrue($out1 instanceof Traversable);

        // check for laziness
        $this->assertEquals(0, $count);

        $i = 0;
        $v2 = [
            (object)["gender" => "M", "name" => "Matt", "family" => "Smith"],
            (object)["gender" => "F", "name" => "Sheila", "family" => "Smith"],
            (object)["gender" => "M", "name" => "Steve", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Cecilia", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Verity", "family" => "Smith"]
        ];
        
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
        $v = [
            (object)["gender" => "M", "name" => "Matt", "family" => "Smith"],
            (object)["gender" => "F", "name" => "Sheila", "family" => "Smith"],
            (object)["gender" => "M", "name" => "Steve", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Cecilia", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Verity", "family" => "Smith"]
        ];
        $out1 = F::transduce(
            F::indexByT(fn($x) => F::prop("gender", $x)),
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
    
    function testThreadable()
    {
        $in = [
            (object)["gender" => "M", "name" => "Matt", "family" => "Smith"],
            (object)["gender" => "F", "name" => "Sheila", "family" => "Smith"],
            (object)["gender" => "M", "name" => "Steve", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Cecilia", "family" => "Jones"],
            (object)["gender" => "F", "name" => "Verity", "family" => "Smith"]
        ];
        $fn = F::indexBy(fn($x) => F::prop("gender", $x));
        $this->assertTrue(is_callable($fn));
        $out = $fn($in);
        $this->assertTrue($out instanceof Traversable);
        $i = 0;
        foreach($out as $k => $v)
        {
            $this->assertEquals($in[$i]->gender, $k);
            $this->assertEquals($in[$i], $v);
            $i++;
        }
    }
    
    function testEarlyCompletion()
    {
        $transducer = F::compose(
            F::indexByT(fn($v, $k) => $k + 1),
            F::partitionByT(fn($v, $k) => intval($k / 2)),
            F::mapT(fn($v) => implode("", $v))
        );
        
        $input = ["a", "b", "c", "d", "e", "f", "g", "h"];
        $out = F::transduce($transducer, fn($acc, $v) => F::append($acc, $v), [], $input);
        $this->assertSame(["a", "bc", "de", "fg", "h"], $out);
    }
}