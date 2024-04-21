<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;
use Traversable;

trait Append 
{
    /**
     * Creates a new keyed sequence which contains all the values from the input
     * sequence and then the passed in key => value pairs appended to the end.
     *
     * Regardless of acc's type, the returned value will always be a lazy
     * Traversable. Otherwise, for arrays, if a key already existed in the array
     * then the new value would overwrite the old value rather than being appended
     * to the end.
     *
     * I.e. keys are not guaranteed to be unique in the returned Traversable.
     *
     * @param iterable|object $seq  input sequence or object with appendK method
     * @param mixed $kvs            an alternating sequence of values followed by 
     *                              an accompanying key
     *
     * @return Traversable|object new sequence or return value from $seq->appendK
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function appendK($seq, ...$kvs)
    {
        if(count($kvs) === 0)
        {
            return $seq;
        }
        else if(count($kvs) % 2 === 1)
        {
            throw new InvalidArgumentException("Each value must have an accompanying key");
        }
        if(is_object($seq) && method_exists($seq, "appendK"))
        {
            return $seq->appendK(...$kvs);
        }
        else if(is_array($seq) || self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($kvs, $seq) {
                yield from $seq;
                for($i = 0; $i < count($kvs); $i+=2)
                {
                    yield $kvs[$i+1] => $kvs[$i];
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }

    /**
     * Creates a new un-keyed sequence which contains all the values from the
     * input sequence followed by the passed in values.
     *
     * @param iterable|object $seq  input sequence or object with "append" method
     * @param mixed $vals           one or more values to append
     * 
     * @return iterable|object new sequence or return value of $seq->append. If
     *                         $vals is empty then return input sequence $seq.
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function append($seq, ...$vals)
    {
        if(count($vals) === 0)
        {
            return $seq;
        }
        else if(is_object($seq) && method_exists($seq, "append"))
        {
            return $seq->append(...$vals);
        }
        else if(is_array($seq))
        {
            $out = array_merge(array_values($seq), array_values($vals));
        }
        else if(self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($vals, $seq) {
                // don't yield from as not preserving keys
                $i = 0;
                foreach($seq as $v)
                {
                    yield $i => $v;
                    $i++;
                }
                foreach($vals as $v)
                {
                    yield $i => $v;
                    $i++;
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }
}
