<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

use RamdaPHP\Core as C;
use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase 
{
    public function testCurry()
    {
        $fnAdd = C::curry(function($arg1, $arg2) {
            return $arg1 + $arg2;
        });

        $this->assertIsCallable($fnAdd);

        $fnAdd5 = $fnAdd(5);

        $this->assertIsCallable($fnAdd5);
        $this->assertSame($fnAdd5(3), 8);
        $this->assertSame($fnAdd5(1), 6);
    }

    public function testPlaceholder()
    {
        $fnSub = C::curry(function($a, $b) {
            return $a - $b;
        });

        $this->assertIsCallable($fnSub);

        $fnSub6 = $fnSub(C::__(), 6);

        $this->assertIsCallable($fnSub6);

        $this->assertSame($fnSub6(10), 4);
        $this->assertSame($fnSub6(15), 9);
    }

    public function testInvoker()
    {
        $obj = new class() {
            public $val = 5;
            function add3() {
                $this->val += 3;
            }
            function sub($a, $b) {
                return $a - $b;
            }
        };
        
        $add3 = C::invoker(0, "add3");
        $add3($obj);
        $this->assertSame($obj->val, 8);
        
        $sub = C::invoker(2, "sub");
        $subFrom10 = $sub(10);
        $out1 = $subFrom10(7, $obj);
        $this->assertSame($out1, 3);
        $out2 = $subFrom10(4, $obj);
        $this->assertSame($out2, 6);
    }
    
    function testAlways()
    {
        $fn1 = C::always(3);
        $this->assertSame($fn1(), 3);
        $fn2 = C::always("hello");
        $this->assertSame($fn2(), "hello");
    }

    function testComplement()
    {
        $fn = C::complement(C::eq());
        $this->assertSame(C::eq("a", "a"), true);
        $this->assertSame($fn("a", "a"), false);
        $this->assertSame(C::eq("a", "b"), false);
        $this->assertSame($fn("a", "b"), true);

        $fn2 = C::eq(5);
        $nfn2 = C::complement($fn2);

        $this->assertSame($fn2(5), true);
        $this->assertSame($nfn2(5), false);
        $this->assertSame($fn2(6), false);
        $this->assertSame($nfn2(6), true);
    }
}
