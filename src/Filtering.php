<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use stdClass;

trait Filtering
{
    public static function filter(...$args)
    {
        $filter = self::curry(function(callable $func, $target) {
            if(method_exists($target, "filter"))
            {
                return $target->filter($func);
            }
            else if(self::isSequentialArray($target))
            {
                return array_values(array_filter($target, $func));
            }
            else if (is_array($target))
            {
                return array_filter($target, $func, ARRAY_FILTER_USE_BOTH );
            }
            else if($target instanceof stdClass)
            {
                $out = new stdClass();
                foreach($target as $k => $v)
                {
                    if($func($v, $k))
                    {
                        $out->$k = $v;
                    }
                }
                return $out;
            }
            else
            {
                $generator = function() use($target, $func) {
                    foreach($target as $k => $v)
                    {
                        if($func($v, $k))
                        {
                            yield $k => $v;
                        }
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $filter(...$args);
    }

    public static function first(...$args)
    {
        $first = self::curry(function(iterable $iterable) {
            if(method_exists($iterable, "first"))
            {
                return $iterable->first();
            }
            else
            {
                $out = null;
                foreach($iterable as $v)
                {
                    $out = $v;
                    break;
                }
                return $out;
            }
        });
        return $first(...$args);
    }

    public static function reject(...$args)
    {
        $reject = self::curry(function(callable $func, iterable $target) {
            return self::filter(self::complement($func), $target);
        });
        return $reject(...$args);
    }

    public static function take(...$args)
    {
        $take = self::curry(function(int $count, iterable $iterable) {
            $generator = function() use($count, $iterable) {
                $i = 0;
                foreach($iterable as $key => $value)
                {
                    if($i < $count)
                    {
                        yield $key => $value;
                        $i++;
                    }
                    else
                    {
                        break;
                    }
                }
            };
            return self::generatorToIterable($generator);
        });
        return $take(...$args);
    }
}