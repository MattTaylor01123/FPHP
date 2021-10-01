<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait InTo
{
    public static function inTo(...$args)
    {
        $inTo = self::curry(function($initial, callable $transducer, $collection) {
            if(is_object($initial))
            {
                return self::transduce($transducer, self::assoc(), $initial, $collection);
            }
            // if the target is an array then assume we need a non-associative (classic)
            // array. If associative array is required then have to use transduce.
            elseif(is_array($initial))
            {
                return self::transduce($transducer, self::append(), $initial, $collection);
            }
            else
            {
                throw new InvalidArgumentException(
                    "Invalid type for input 'collection'"
                );
            }
        });
        return $inTo(...$args);
    }
}