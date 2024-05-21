<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait Flatten
{
    /**
     * flatten transducer
     * 
     * @return callable transducer
     */
    public static function flattenT() : callable
    {
        $i = 0;
        return fn(callable $step) => self::multiArityfunction(
            fn() => $step(),
            fn($acc) => $step($acc),
            function($acc, $v, $k) use(&$i, $step) {
                return self::reduce(function($acc, $v) use(&$i, $step) {
                    return $step($acc, $v, $i++);
                }, $acc, is_iterable($v) ? $v : [$v]);
            }
        );
    }
    
    /**
     * flatMap transducer
     * 
     * @param callable $transform       map transformation function
     * 
     * @return callable transducer
     */
    public static function flatMapT(callable $transform) : callable
    {
        return self::pipe(
            self::flattenT(),
            self::mapT($transform));
    }
    
    /**
     * Applies a map transformation to every element in the sequence, and then
     * flattens the sequence one level.
     * 
     * @param callable $transform       transformation function
     * @param iterable|null $sequence   the sequence to map & flatten. Threadable
     * 
     * @return mixed     the mapped & flattened sequence. If $sequence is null
     * then a callable is returned.
     */
    public static function flatMap(callable $transform, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::flatMap($transform, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "flatMap"))
        {
            return $sequence->flatMap($transform);
        }
        else
        {
            return self::transduce(
                self::flatMapT($transform), 
                fn($acc, $v) => self::append($acc, $v), 
                self::emptied($sequence), 
                $sequence
            );
        }
    }

    /**
     * Flattens a sequence by one level
     * 
     * @param iterable|null $sequence       sequence to flatten. Threadable
     * 
     * @return mixed the flattened sequence. If $sequence is null then a
     * callable is returned.
     */
    public static function flatten(?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::flatten($sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "flatten"))
        {
            return $sequence->flatten();
        }
        else
        {
            return self::transduce(
                self::flattenT(), 
                fn($acc, $v) => self::append($acc, $v), 
                self::emptied($sequence), 
                $sequence
            );
        }
    }
}