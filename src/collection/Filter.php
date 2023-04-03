<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

use InvalidArgumentException;

trait Filter
{
    /**
     * filter transducer
     *
     * @param callable $predicate       test applied to each value passed in
     *
     * @return callable transducer
     */
    public static function filterT(callable $predicate) : callable
    {
        return fn(callable $step) => fn($acc, $v, $k) => ($predicate($v, $k) ? $step($acc, $v, $k) : $acc);
    }

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function retains keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             optional, collection to filter, threadable
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function. If $target was null then callable.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator.
     */
    public static function filterK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::filterK($predicate, $target);
        }
        if(is_array($target))
        {
            $out = array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH );
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            // preserve keys
            $out = self::transduce(
                self::filterT($predicate),
                self::defaultStepK($target),
                self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function ignores keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             optional, collection to filter, threadable
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator. If $target was null then callable.
     */
    public static function filter(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::filter($predicate, $target);
        }
        if(is_array($target))
        {
            $out = array_values(array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH));
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            $notTravOrGen = !($target instanceof \Traversable || self::isGenerator($target));
            // use the transduce filter, but ignore key
            $out = self::transduce(
                self::filterT($predicate),
                self::defaultStep($target),
                $notTravOrGen ? [] : self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }
}