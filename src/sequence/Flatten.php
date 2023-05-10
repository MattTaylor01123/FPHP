<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use FPHP\utilities\IterableGenerator;

trait Flatten
{
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
            $transformFlatMap = self::pipe(
                fn($coll) => self::map($transform, $coll),
                fn($coll) => self::flatten($coll)
            );
            return $transformFlatMap($sequence);
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
            $generator = function() use($sequence) {
                foreach($sequence as $v)
                {
                    if(is_iterable($v))
                    {
                        yield from $v;
                    }
                    else
                    {
                        yield $v;
                    }
                }
            };
            $iterable = new IterableGenerator($generator);
            if(is_array($sequence))
            {
                return iterator_to_array($iterable, false);
            }
            else
            {
                return $iterable;
            }
        }
    }
}