<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\utilities;

use FPHP\FPHP as F;
use FPHP\utilities\IterableGenerator;
use PHPUnit\Framework\TestCase;
use tests\TestUtils;

final class IterableGeneratorTest extends TestCase
{
    use TestUtils;

    function dpTestJson()
    {
        return [
            [$this->getGenIdx(), '[20,40,60,80]'],
            [$this->getGenAssoc(), '{"i":20,"j":40,"k":60,"l":80}']
        ];
    }

    /**
     * @dataProvider dpTestJson
     */
    public function testJson($in, $exp)
    {
        $act = F::map(fn($v) => $v * 2, new IterableGenerator($in));
        $this->assertEquals($exp, json_encode($act));
    }
}