<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;

trait Map
{
    /**
     * map transducer
     * 
     * @param callable $transform    transform function
     * 
     * @return callable transducer
     */
    public static function mapT(callable $transform) : callable
    {
        // multi-arity transducer...
        return fn(callable $step) => self::multiArityfunction(
            fn() => $step(),
            fn($acc) => $step($acc),
            fn($acc, $v, $k) => $step($acc, $transform($v, $k), $k)
        );
    }

    /**
     * Uses a transformation function to map each value in a sequence onto a new value
     * in a new sequence.
     * 
     * @param callable $transform           transformation function
     * @param iterable|null $sequence       sequence
     * 
     * @return iterable|callable    a new sequence containing the transformed values,
     * unless sequence is null, in which case a callable is returned.
     * 
     * @throws InvalidArgumentException if sequence is not an array, traversable,
     * generator, functor.
     */
    public static function map(callable $transform, $sequence = null)
    {
        if(!$sequence)
        {
            return fn(iterable $sequence) => self::map($transform, $sequence);
        }
        else if(is_object($sequence) && method_exists($sequence, "map"))
        {
            $out = $sequence->map($transform);
        }
        // array_map callback doesn't support keys
        else if(is_array($sequence) || ($sequence instanceof \Traversable) || self::isGenerator($sequence))
        {
            // map preserves keys, so use K step
            $out = self::transduce(
                self::mapT($transform),
                self::defaultStepK($sequence),
                self::emptied($sequence),
                $sequence
            );
        }
        else
        {
            throw new InvalidArgumentException("'sequence' must be one of array, traversable, generator, functor.");
        }
        return $out;
    }
}