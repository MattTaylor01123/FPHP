<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait InTo
{
    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "append" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param mixed $target             values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function inTo($initial, callable $transducer, $target)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $target);
    }

    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "assoc" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param mixed $target             values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function intoK($initial, callable $transducer, $target)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::assoc($acc, $v, $k), $initial, $target);
    }
}