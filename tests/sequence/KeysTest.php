<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;
use Traversable;

final class KeysTest extends TestCase
{
    use TestUtils;

    function testKeysIndexed()
    {
        $v = ["a","b","c","d","e"];
        $out1 = F::keys($v);
        $this->assertSame($out1, [0, 1, 2, 3, 4]);
    }
    
    function testKeysAssoc()
    {
        $v = ["a" => 1,"b" => 2,"c" => 3,"d" => 4,"e" => 5];
        $out1 = F::keys($v);
        $this->assertSame($out1, ["a", "b", "c", "d", "e"]);
    }

    function testKeysOverride()
    {
        $collection = $this->buildCollectionMock("keys", null, ["hello", "world"]);
        $out2 = F::keys($collection);
        $this->assertSame($out2, ["hello", "world"]);
    }

    function testKeysItIdx()
    {
        $v = F::generatorToIterable(fn() => yield from [10, 20, 30, 40]);
        $out = F::keys($v);
        $this->assertTrue($out instanceof Traversable);
        $this->assertEquals(iterator_to_array($out, false), [0,1,2,3]);
    }

    function testKeysItAssoc()
    {
        $v = F::generatorToIterable(fn() => yield from ["i" => 10, "j" => 20, "k" => 30, "l" => 40]);
        $out = F::keys($v);
        $this->assertTrue($out instanceof Traversable);
        $this->assertSame(iterator_to_array($out, false), ["i","j","k","l"]);
    }
    
    function testThreadable()
    {
        $v = ["a","b","c","d","e"];
        $fn = F::keys();
        $this->assertTrue(is_callable($fn));
        $out1 = $fn($v);
        $this->assertSame($out1, [0, 1, 2, 3, 4]);
    }
    
    function testEarlyCompletion()
    {
        $transducer = F::compose(
            F::keysT(),
            F::partitionByT(fn($v, $k) => intval($k / 3)),
            F::mapT(fn($v) => implode("", $v))
        );
        
        $input = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5, "f" => 6, "g" => 7, "h" => 8];
        $out = F::transduce($transducer, fn($acc, $v) => F::append($acc, $v), [], $input);
        $this->assertSame(["abc", "def", "gh"], $out);
    }
}