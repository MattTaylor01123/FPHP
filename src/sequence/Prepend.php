<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;
use Traversable;

trait Prepend 
{
    // Key point
    // ---------
    // prepend and prependK cannot be variadic (cannot prepend multiple values)
    // because the transducers and reducers use the same function signature, i.e
    // fn($acc, $v, $k).
    // transducers call the reducer always passing in K, in case the reducer is
    // keyed. a non-keyed reducer only takes two params and so just ignores K.
    
    /**
     * Creates a new keyed sequence which contains all the values from the input
     * sequence with the passed in key => value pair prepended to the start.
     *
     * Regardless of seq's type, the returned value will always be a lazy
     * Traversable. Otherwise, for arrays, if a key already existed in the array
     * then the new value would overwrite the old value rather than being appended
     * to the end.
     *
     * I.e. keys are not guaranteed to be unique in the returned Traversable.
     *
     * @param iterable|object $seq  input sequence or object with prependK method
     * @param mixed $v              value to prepend
     * @param mixed $k              key to prepend
     *
     * @return Traversable|object new sequence or return value from $seq->prependK
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function prependK($seq, $v = "__DEF__", $k = "__DEF__")
    {
        // arity 1 - return the sequence
        if($v === "__DEF__" && $k === "__DEF__")
        {
            if(is_object($seq) && method_exists($seq, "prependK"))
            {
                return $seq->prependK();
            }
            return $seq;
        }
        
        // invalid arity
        if($v === "__DEF__" || $k === "__DEF__")
        {
            throw new InvalidArgumentException("'prependK' - invalid arity");
        }
        
        // arity 3 - prepend the k => v
        if(is_object($seq) && method_exists($seq, "prependK"))
        {
            return $seq->prependK($v, $k);
        }
        if(is_array($seq) || self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($v, $k, $seq) {
                yield $k => $v;
                yield from $seq;
            };
            return self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'seq' must be of type array or traversable");
        }
    }

    /**
     * Creates a new un-keyed sequence which contains all the values from the
     * input sequence preceded by the passed in value.
     *
     * @param iterable|object $seq  input sequence or object with "prepend" method
     * @param mixed $v              value to prepend
     * 
     * @return iterable|object new sequence or return value of $seq->prepend. If
     *                         $vals is empty then return input sequence $seq.
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function prepend($seq, $v = "__DEF__")
    {
        // arity 1 - return the sequence
        if($v === "__DEF__")
        {
            if(is_object($seq) && method_exists($seq, "prepend"))
            {
                return $seq->prepend();
            }
            return $seq;
        }
        
        // arity 2 - prepend value
        if(is_object($seq) && method_exists($seq, "prepend"))
        {
            return $seq->prepend($v);
        }
        if(is_array($seq))
        {
            return [$v, ...array_values($seq)];
        }
        else if(self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($v, $seq) {
                // don't yield from as not preserving keys
                $i = 0;
                yield $i => $v;
                $i++;
                foreach($seq as $val)
                {
                    yield $i => $val;
                    $i++;
                }
            };
            return self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'seq' must be of type array or traversable");
        }
    }
}
