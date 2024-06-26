<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Keys
{
    /**
     * Keys transducer
     * 
     * @return callable transducer function
     */
    public static function keysT() : callable
    {
        $i = 0;
        return function(callable $step) use(&$i) { 
            return self::multiArityfunction(
                fn() => $step(),
                fn($acc) => $step($acc),
                function($acc, $v, $k) use(&$i, $step) {
                    return $step($acc, $k, $i++);
                }
            );
        };
    }

    /**
     * Get the keys of a sequence
     *
     * @param mixed $sequence             optional, sequence, threadable
     *
     * @return iterable|callable a new sequence containing the keys.  If
     * $sequence was null then callable.
     *
     * @throws InvalidArgumentException if target is not an iterable or object
     * with a 'keys' method.
     */
    public static function keys($sequence = null)
    {
        if(!$sequence)
        {
            $out = fn(iterable $sequence) => self::keys($sequence);
        }
        else if(is_object($sequence) && method_exists($sequence, "keys"))
        {
            $out = $sequence->keys();
        }
        else if(is_array($sequence))
        {
            $out = array_keys($sequence);
        }
        else if(is_iterable($sequence))
        {
            $out = self::transduce(
                self::keysT(),
                fn($acc, $v) => self::append($acc, $v),
                self::emptied($sequence),
                $sequence
            );
        }
        else
        {
            throw new InvalidArgumentException("'sequence' must be iterable or object that has a 'keys' method.");
        }
        return $out;
    }
}