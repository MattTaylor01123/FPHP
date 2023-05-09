<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait InTo
{
    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "append" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param iterable $sequence        values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function inTo($initial, callable $transducer, iterable $sequence)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $sequence);
    }

    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "assoc" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param iterable $sequence        values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function intoK($initial, callable $transducer, iterable $sequence)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::appendK($acc, $v, $k), $initial, $sequence);
    }
}