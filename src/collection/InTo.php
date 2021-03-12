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
            if(is_object($initial) || is_array($initial))
            {
                return self::transduce($transducer, self::assoc(), $initial, $collection);
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