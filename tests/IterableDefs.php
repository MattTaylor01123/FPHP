<?php

/*
 * (c) Matthew Taylor
 */

namespace tests;

trait IterableDefs
{
    function getIndexedArray()
    {
        return [1,2,3,4,5];
    }

    function getAssocArray()
    {
        return [
            "a" => 1,
            "b" => 2,
            "c" => 3,
            "d" => 4,
            "e" => 5
        ];
    }

    function getObj()
    {
        return (object)[
            "f" => 2,
            "g" => 4,
            "h" => 6
        ];
    }

    function getItIdx()
    {
        yield 10;
        yield 20;
        yield 30;
        yield 40;
    }

    function getItAssoc()
    {
        yield "i" => 10;
        yield "j" => 20;
        yield "k" => 30;
        yield "l" => 40;
    }

    function getPersonsDataIdx()
    {
        return [
            (object)[
                "gender" => "M",
                "name" => "Matt",
                "family" => "Smith"
            ],
            (object)[
                "gender" => "F",
                "name" => "Sheila",
                "family" => "Smith"
            ],
            (object)[
                "gender" => "M",
                "name" => "Steve",
                "family" => "Jones"
            ],
            (object)[
                "gender" => "F",
                "name" => "Cecilia",
                "family" => "Jones"
            ],
            (object)[
                "gender" => "F",
                "name" => "Verity",
                "family" => "Smith"
            ]
        ];
    }

    function getPersonsDataIt()
    {
        yield from $this->getPersonsDataIdx();
    }
}