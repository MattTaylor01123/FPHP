<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use PHPUnit\Framework\TestCase;
use RamdaPHP\RamdaPHP as R;
use Traversable;

final class ConcatTest extends TestCase
{
    use TestUtils;

    public function testConcatSeq()
    {
        $a = $this->getIndexedArray();
        $b = $this->getIndexedArray();
        $out = R::concat($a, $b);
        $exp = [1,2,3,4,5,1,2,3,4,5];
        $this->assertSame($exp, $out);
    }

    public function testConcatAssoc()
    {
        $a = $this->getAssocArray();
        $b = $this->getAssocArray();
        $out = R::concat($a, $b);
        $exp = [1,2,3,4,5,1,2,3,4,5];
        $this->assertSame($exp, $out);
    }

    public function testSeqIt()
    {
        $a = $this->getItIdx();
        $b = $this->getItIdx();
        $out = R::concat($a, $b);
        $this->assertTrue($out instanceof Traversable);
        $exp = [10,20,30,40,10,20,30,40];
        $this->assertEquals($exp, iterator_to_array($out, true));
    }

    public function testAssocIt()
    {
        $a = $this->getItAssoc();
        $b = $this->getItAssoc();
        $out = R::concat($a, $b);
        $this->assertTrue($out instanceof Traversable);
        $exp = [10,20,30,40,10,20,30,40];
        $this->assertEquals($exp, iterator_to_array($out, true));
    }
}