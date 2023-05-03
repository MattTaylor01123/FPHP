<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class PropsTest extends TestCase
{
    use TestUtils;
    
    public function testPropsObject()
    {
        $o1 = F::props(["f", "i","h"], $this->getObj());
        $e1 = [2, null, 6];
        $this->assertEquals($o1, $e1);
    }

    public function testPropsArray()
    {
        $o1 = F::props(["a", "f","c"], $this->getAssocArray());
        $e1 = [1, null, 3];
        $this->assertEquals($o1, $e1);
    }
}