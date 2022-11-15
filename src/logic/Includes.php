<?php

/*
 * (c) Matthew Taylor
 */

namespace src\logic;

final class Includes
{
    public static function includes($v, $target)
    {
        if(is_object($target) && method_exists($target, "includes"))
        {
            $out = $target->includes($v);
        }
        else if(is_iterable($target))
        {
            $out = self::reduce(fn($acc, $v2) => $v === $v2 ? new Reduced(true) : false, false, $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must have method 'includes' or be iterable.");
        }
        return $out;
    }

    /**
     * Checks if all given values are within the given list.
     *
     * @param iterable $vals        the values to search for
     * @param iterable $list        the list to search within
     *
     * @return bool True if all values in list, false otherwise
     */
    public static function includesAll(iterable $vals, iterable $list)
    {
        return self::all(fn($v) => self::includes($v, $list), $vals);
    }

    /**
     * Checks if any given values are within the given list.
     *
     * @param iterable $vals        the values to search for
     * @param iterable $list        the list to search within
     *
     * @return bool True if any values in list, false otherwise
     */
    public static function includesAny(iterable $vals, iterable $list)
    {
        return self::any(fn($v) => self::includes($v, $list), $vals);
    }
}