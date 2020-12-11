<?php

/*
 * (c) Matthew Taylor
 */
namespace RamdaPHP;

trait Mapping 
{
    public static function indexBy(...$args)
    {
        $indexBy = self::curry(function(callable $func, iterable $iterable) {
            if(method_exists($iterable, "indexBy"))
            {
                return $iterable->indexBy($func);
            }
            else if(is_array($iterable))
            {
                $out = array();
                foreach($iterable as $k => $v)
                {
                    $key = $func($v, $k);
                    $out[$key] = $v;
                }
                return $out;
            }
            else
            {
                $generator = function() use($iterable, $func) {
                    foreach($iterable as $k => $v)
                    {
                        yield $func($v, $k) => $v;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $indexBy(...$args);
    }

    public static function map(...$args)
    {
        $map = self::curry(function(callable $func, $target) {
            if(method_exists($target, "map"))
            {
                return $target->map($func);
            }
            else if(is_array($target) || !is_iterable($target))
            {
                $mapped = array();
                foreach($target as $k => $v)
                {
                    $mapped[$k] = $func($v, $k);
                }
                return is_array($target) ? $mapped : (object)$mapped;
            }
            else
            {
                $generator = function() use($target, $func) {
                    foreach($target as $k => $v)
                    {
                        yield $k => $func($v, $k);
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $map(...$args);
    }

    public static function pluck(...$args)
    {
        $pluck = self::curry(function(string $propName, iterable $iterable) {
            if(method_exists($iterable, "pluck"))
            {
                $out = $iterable->pluck($propName);
            }
            else if(is_array($iterable))
            {
                $out = array_column($iterable, $propName);
            }
            else
            {
                $out = RamdaPHP::map(self::prop($propName), $iterable);
            }
            return $out;
        });
        return $pluck(...$args);
    }
}
