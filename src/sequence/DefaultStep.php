<?php

/**
 * (c) Matthew Taylor
 */

namespace src\sequence;

use Exception;
use Traversable;

trait DefaultStep 
{
    /**
     * Returns an appropriate (non key preserving) step function for the input
     * 
     * @param object|array $target      input to return appropriate step 
     *                                  function for
     * 
     * @return callable     step function
     * 
     * @throws Exception if a suitable step function could not be found.
     */
    public static function defaultStep($target)
    {
        return fn($acc, $v) => self::append($acc, $v);
    }
    
    /**
     * Returns an appropriate key preserving step function for the input
     * 
     * @param object|array $target      input to return appropriate step 
     *                                  function for
     * 
     * @return callable     step function
     * 
     * @throws Exception if a suitable step function could not be found.
     */
    public static function defaultStepK($target)
    {
        if(is_object($target) && $target instanceof Traversable)
        {
            $out = fn($acc, $v, $k) => self::appendK($acc, $v, $k);
        }
        else if(is_array($target) || is_object($target))
        {
            $out = fn($acc, $v, $k) => self::assoc($acc, $v, $k);
        }
        else
        {
            throw new Exception("Not possible to determine a step function for type " . gettype($target));
        }
        return $out;
    }
}
