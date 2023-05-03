<?php

/*
 * (c) Matthew Taylor
 */

namespace tests\sequence;

use FPHP\FPHP as F;
use PHPUnit\Framework\TestCase;

final class MatchTest extends TestCase
{
    public function cases()
    {
        return [
            [["name" => fn($v) => $v === "Emma", "age" => fn($v) => $v === 25], [(object)["name" => "Emma", "age" => 25]]],
            [["name" => fn($v) => $v === "Emma"],                               [(object)["name" => "Emma", "age" => 36],
                                                                                 (object)["name" => "Emma", "age" => 25]]],
            [["name" => fn($v) => $v === "Steve"],                              []],
            [["age" =>  fn($v) => $v > 30],                                     [(object)["name" => "Annie", "age" => 45],
                                                                                 (object)["name" => "Emma", "age" => 36]]]
        ];
    }

    /**
     * @dataProvider cases
     */
    public function testArray($criteria, $expected)
    {
        $data = [
            (object)["name" => "Chris", "age" => 25],
            (object)["name" => "Annie", "age" => 45],
            (object)["name" => "Emma", "age" => 36],
            (object)["name" => "Emma", "age" => 25]
        ];

        $res = F::match($criteria, $data);

        $this->assertEquals($expected, $res);
    }

    /**
     * @dataProvider cases
     */
    public function testGenerator($criteria, $expected)
    {
        $generator = fn() => yield from [
            (object)["name" => "Chris", "age" => 25],
            (object)["name" => "Annie", "age" => 45],
            (object)["name" => "Emma", "age" => 36],
            (object)["name" => "Emma", "age" => 25]
        ];

        $res = F::match($criteria, $generator());

        $this->assertEquals($expected, iterator_to_array($res, false));
    }

    public function testLaziness()
    {
        $generator = fn() => yield from [
            (object)["name" => "Chris", "age" => 25],
            (object)["name" => "Annie", "age" => 45],
            (object)["name" => "Emma", "age" => 36],
            (object)["name" => "Emma", "age" => 25]
        ];

        $i = 0;
        $func = F::pipe(
            F::tap(function() use(&$i) {
                $i = $i + 1;
            }),
            fn($c) => F::match(["age" => fn($v) => $v > 30], $c),
            fn($c) => F::take(1, $c)
        );

        $res = $func($generator());

        $this->assertEquals(0, $i);
        $this->assertEquals([(object)["name" => "Annie", "age" => 45]], iterator_to_array($res, false));
        $this->assertEquals(2, $i);
    }
}