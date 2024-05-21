<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

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
    public static function adjustT($idx, callable $transform) : callable
    {
        return fn(callable $step) => self::multiArityfunction(
            fn() => $step(),
            fn($acc) => $step($acc),
            fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k)
        );
    }

    /*
     * Produces a new collection which contains all the values from the input collection,
     * except that the value at the given index has been transformed by the given transformation
     * function.
     *
     * @param string|int $idx       index of value in input collection to tramsform
     * @param callable $transform   transformation function to apply to value
     * @param iterable $collection  input collection - threadable.
     *
     * @return iterable|callable a new collection, or a callable if $collection was null
     */
    public static function adjust($idx, callable $transform, ?iterable $collection = null)
    {
        if($collection === null)
        {
            return fn($collection) => self::adjust($idx, $transform, $collection);
        }
        return self::transduce(
            self::adjustT($idx, $transform),
            // preserve keys (array itself isn't mutated, only elements)
            self::defaultStepK($collection),
            self::emptied($collection),
            $collection
        );
    }
}
