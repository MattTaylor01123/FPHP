<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Values
{
    /**
     * values transducer
     * 
     * @param callable $step
     * 
     * @return callable transducer
     */
    public static function valuesT(callable $step) : callable
    {
        return function($acc, $v) use($step) {
            return $step($acc, $v);
        };
    }

    /**
     * Extracts the values from a collection or the properties from an object
     * 
     * @param iterable|object $target       the collection or object
     * 
     * @return mixed    the values or properties
     * 
     * @throws InvalidArgumentException if $target is not an iterable or an object
     */
    public static function values($target)
    {
        $transduceInto = fn($initial, $target) => self::transduce(
            fn($step) => self::valuesT($step),
            fn($acc, $v) => self::append($acc, $v),
            $initial,
            $target
        );
        if(is_object($target) && method_exists($target, "values"))
        {
            $out = $target->values();
        }
        else if(is_array($target))
        {
            $out = array_values($target);
        }
        else if(is_iterable($target))
        {
            $out = $transduceInto(self::emptied($target), $target);
        }
        else if(is_object($target))
        {
            $out = $transduceInto(self::emptied([]), $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must be iterable or object");
        }
        return $out;
    }
}