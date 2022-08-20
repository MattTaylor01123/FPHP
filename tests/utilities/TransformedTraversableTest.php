<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\utilities;

use FPHP\FPHP as F;
use FPHP\utilities\TransformedTraversable;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class TransformedTraversableTest extends TestCase
{
    use TestUtils;

    function dpTestJson()
    {
        return [
            [$this->getItIdx(), '[20,40,60,80]'],
            [$this->getItAssoc(), '{"i":20,"j":40,"k":60,"l":80}']
        ];
    }

    /**
     * @dataProvider dpTestJson
     */
    public function testJson($in, $exp)
    {
        $act = new TransformedTraversable(F::mapT(fn($v) => $v * 2), fn($acc, $v, $k) => F::assoc($acc, $v, $k), $in);
        $this->assertEquals($exp, json_encode($act));
    }
}