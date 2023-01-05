<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Adjust 
{
    /**
     * Creates a transducer function.
     *
     * Every value passed into the transducer function is passed to the step
     * function, except that the value at the given index is transformed using
     * the given transformation function before being passed to the step function.
     *
     * @param mixed $idx            index
     * @param callable $transform   transformation function
     * @param callable $step        step function
     *
     * @return callable transducer
     */
    public static function adjustT($idx, callable $transform, callable $step) : callable
    {
        return fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
    }

    /*
     * Produces a new collection which contains all the values from the input collection,
     * excep that the value at the given index has been transformed by the given transformation
     * function.
     *
     * @param string|int $idx       index of value in input collection to tramsform
     * @param callable $transform   transformation function to apply to value
     * @param mixed $collection     input collection
     *
     * @return mixed a new collection
     */
    public static function adjust($idx, callable $transform, $collection)
    {
        return self::transduce(
            fn($step) => self::adjustT($idx, $transform, $step),
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Adjust preserves keys anyway, so using assoc is fine.
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
        );
    }
}
