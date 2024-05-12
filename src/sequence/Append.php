<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;
use Traversable;

trait Append 
{
    // Key point
    // ---------
    // append and appendK cannot be variadic (cannot append multiple values)
    // because the transducers and reducers use the same function signature, i.e
    // fn($acc, $v, $k).
    // transducers call the reducer always passing in K, in case the reducer is
    // keyed. a non-keyed reducer only takes two params and so just ignores K.
    
    
    /**
     * Creates a new keyed sequence which contains all the values from the input
     * sequence and then the passed in key => value pair appended to the end.
     *
     * Regardless of acc's type, the returned value will always be a lazy
     * Traversable. Otherwise, for arrays, if a key already existed in the array
     * then the new value would overwrite the old value rather than being appended
     * to the end.
     *
     * I.e. keys are not guaranteed to be unique in the returned Traversable.
     *
     * @param iterable|object $seq  input sequence or object with appendK method
     * @param mixed $v              value to append
     * @param mixed $k              key to append
     *
     * @return Traversable|object new sequence or return value from $seq->appendK
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function appendK($seq, $v = "__DEF__", $k = "__DEF__")
    {
        // arity 1 - return the sequence
        if($v === "__DEF__" && $k === "__DEF__")
        {
            if(is_object($seq) && method_exists($seq, "appendK"))
            {
                return $seq->appendK();
            }
            return $seq;
        }
        
        // invalid arity
        if($v === "__DEF__" || $k === "__DEF__")
        {
            throw new InvalidArgumentException("'prependK' - invalid arity");
        }
        
        // arity 3 - append the k => v
        if(is_object($seq) && method_exists($seq, "appendK"))
        {
            return $seq->appendK($v, $k);
        }
        else if(is_array($seq) || self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($v, $k, $seq) {
                yield from $seq;
                yield $k => $v;
            };
            return self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
    }

    /**
     * Creates a new un-keyed sequence which contains all the values from the
     * input sequence followed by the passed in value.
     *
     * @param iterable|object $seq  input sequence or object with "append" method
     * @param mixed $v              value to append
     * 
     * @return iterable|object new sequence or return value of $seq->append. If
     *                         $vals is empty then return input sequence $seq.
     *
     * @throws InvalidArgumentException if input sequence is not an array or a
     * traversable.
     */
    public static function append($seq, $v = "__DEF__")
    {
        // arity 1 - return the sequence
        if($v === "__DEF__")
        {
            if(is_object($seq) && method_exists($seq, "append"))
            {
                return $seq->append();
            }
            return $seq;
        }
        
        // arity 2 - prepend value
        if(is_object($seq) && method_exists($seq, "append"))
        {
            return $seq->append($v);
        }
        else if(is_array($seq))
        {
            $out = array_values($seq);
            $out[] = $v;
            return $out;
        }
        else if(self::isTraversable($seq) || self::isGenerator($seq))
        {
            $fn = function() use($v, $seq) {
                // don't yield from as not preserving keys
                $i = 0;
                foreach($seq as $val)
                {
                    yield $i => $val;
                    $i++;
                }
                yield $i => $v;
            };
            return self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
    }
}
