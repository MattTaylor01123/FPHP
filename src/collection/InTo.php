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
            $transInto = self::transduce($transducer, self::__(), $initial, $collection);
            if(is_object($initial) || is_array($initial))
            {
                return $transInto(self::assoc());
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