<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Reducing
{
    public static function all(...$args)
    {
        $all = self::curry(function(callable $func, iterable $iterable) {
            if(method_exists($iterable, "all"))
            {
                return $iterable->all($func);
            }
            else
            {
                foreach($iterable as $k => $v)
                {
                    if(!$func($v, $k))
                    {
                        return false;
                    }
                }
                return true;
            }
        });
        return $all(...$args);
    }

    public static function any(...$args)
    {
        $any = self::curry(function(callable $func, iterable $iterable) {
            if(method_exists($iterable, "any"))
            {
                return $iterable->any($func);
            }
            else
            {
                foreach($iterable as $k => $v)
                {
                    if($func($v, $k))
                    {
                        return true;
                    }
                }
                return false;
            }
        });
        return $any(...$args);
    }

    public static function find(...$args)
    {
        $find = self::curry(function(callable $predicate, iterable $iterable) {
            if(method_exists($iterable, "find"))
            {
                return $iterable->length();
            }
            else
            {
                foreach($iterable as $k => $v)
                {
                    if($predicate($v, $k))
                    {
                        return $v;
                    }
                }
                return null;
            }
        });
        return $find(...$args);
    }

    public static function groupBy(...$args)
    {
        $groupBy = self::curry(function(callable $fn, iterable $target) {
            $out = array();
            foreach($target as $k => $v)
            {
                $g = $fn($v, $k);
                if(!self::hasProp($g, $out))
                {
                    $out[$g] = [];
                }
                $out[$g][] = $v;
            }
            return $out;
        });
        return $groupBy(...$args);
    }

    public static function includes(...$args)
    {
        $includes = self::curry(function($v, iterable $iterable) {
            foreach($iterable as $itV)
            {
                if($itV === $v)
                {
                    return true;
                }
            }
            return false;
        });
        return $includes(...$args);
    }

    /**
     * Checks if all given values are within the given list.
     *
     * @param iterable $vals        the values to search for
     * @param iterable $list        the list to search within
     *
     * @return bool True if all values in list, false otherwise
     */
    public static function includesAll(...$args)
    {
        $includesAll = self::curry(function(iterable $vals, iterable $list) {
            return self::all(self::includes(self::__(), $list), $vals);
        });
        return $includesAll(...$args);
    }

    public static function includesAny(...$args)
    {
        $includesAll = self::curry(function(iterable $vals, iterable $list) {
            return self::any(self::includes(self::__(), $list), $vals);
        });
        return $includesAll(...$args);
    }

    public static function indexOf(...$args)
    {
        $indexOf = self::curry(function($needle, iterable $iterable) {
            if(method_exists($iterable, "indexOf"))
            {
                return $iterable->length();
            }
            elseif(is_array($iterable))
            {
                return array_search($needle, $iterable, true) ?: -1;
            }
            else
            {
                foreach($iterable as $k => $v)
                {
                    if($v === $needle)
                    {
                        return $k;
                    }
                }
                return -1;
            }
        });
        return $indexOf(...$args);
    }

    public static function joinUp(...$args)
    {
        $join = self::curry(function($glue, iterable $iterable) {
            if(method_exists($iterable, "joinUp"))
            {
                return $iterable->joinUp($glue, $iterable);
            }
            else
            {
                // exclude keys, we don't need them, and if include them in
                // iterator_to_array call, values with duplicate keys will be
                // overwritten
                $arr = is_array($iterable) ? $iterable : iterator_to_array($iterable, false);
                return implode($glue, $arr);
            }
        });
        return $join(...$args);
    }

    public static function length(...$args)
    {
        $length = self::curry(function(iterable $iterable) {
            if(method_exists($iterable, "length"))
            {
                return $iterable->length();
            }
            elseif(is_array($iterable))
            {
                return count($iterable);
            }
            else
            {
                $out = self::reduce(function($count) {
                    return ++$count;
                }, 0, $iterable);
                return $out;
            }
        });

        return $length(...$args);
    }

    public static function reduce(...$args)
    {
        $reduce = self::curry(function(callable $func, $initial, $iterable) {
            if(method_exists($iterable, "reduce"))
            {
                return $iterable->reduce($func, $initial);
            }
            else
            {
                $out = $initial;
                foreach($iterable as $k => $v)
                {
                    $out = $func($out, $v, $k);
                    if($out instanceof collection\Reduced)
                    {
                        return $out->v;
                    }
                }
                return $out;
            }
        });
        return $reduce(...$args);
    }
}