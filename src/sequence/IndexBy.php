<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;
use Traversable;

trait IndexBy
{
    /**
     * indexBy transducer
     * 
     * @param callable $func        mapping function
     * 
     * @return callable transducer function
     */
    public static function indexByT(callable $func) : callable
    {
        return fn(callable $step) => fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
    }

    /**
     * Creates a new sequence indexed by the result of calling the mapping
     * function on each element.
     * 
     * @param callable $func                maps sequence values onto keys
     * @param iterable|object $sequence     the sequence to index. Threadable
     * 
     * @return iterable|object|callable  same as type of $sequence, or else 
     * callable if $sequence is null.
     * 
     * @throws InvalidArgumentException if $sequence is not an iterable or an
     * object with a method called "indexBy".
     */
    public static function indexBy(callable $func, $sequence = null)
    {
        if(is_null($sequence))
        {
            return fn($sequence) => self::indexBy($func, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "indexBy"))
        {
            $out = $sequence->indexBy($func);
        }
        else if(is_array($sequence) || $sequence instanceof Traversable)
        {
            $out = self::transduce(
                self::indexByT($func),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($sequence),
                $sequence
            );
        }
        else
        {
            throw new InvalidArgumentException("Invalid argument type for 'sequence'");
        }
        return $out;
    }
}