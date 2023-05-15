<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

trait First
{
    /**
     * Returns the first element in the sequence
     * 
     * @param mixed $target     an iterable or an object that implements
     *                          "first". Threadable.
     * 
     * @return mixed            the first element in the sequence
     * 
     * @throws InvalidArgumentException if $target is not iterable and is not
     * an object that implements "first".
     */
    public static function first($target = null)
    {
        if($target === null)
        {
            return fn($target) => self::first($target);
        }
        if(is_object($target) && method_exists($target, "first"))
        {
            return $target->first();
        }
        else if(is_iterable($target))
        {
            $out = null;
            foreach($target as $v)
            {
                $out = $v;
                break;
            }
            return $out;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
    }
}