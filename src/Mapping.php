<?php

/*
 * (c) Matthew Taylor
 */
namespace RamdaPHP;

use ReflectionClass;
use stdClass;

trait Mapping 
{
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
    
    public static function columns(...$args)
    {
        $columns = self::curry(function(array $properties, iterable $iterable) {
            if(method_exists($iterable, "columns"))
            {
                $out = $iterable->columns($properties);
            }
            else
            {
                $out = RamdaPHP::map(self::props($properties), $iterable);
            }
            return $out;
        });
        return $columns(...$args);
    }

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
    
    public static function mapTo(...$args)
    {
        $mapTo = self::curry(function(string $className, iterable $iterable) {
            if(method_exists($iterable, "mapTo"))
            {
                return $iterable->mapTo($className);
            }
            else
            {
                $type = new ReflectionClass($className);
                $params = self::pipex(
                    $type->getConstructor()->getParameters(),
                    self::indexBy(self::invoker(0, "getName"))
                );
                return self::map(function($v) use($params, $type) {
                    if(is_array($v))
                    {
                        $in = $v;
                    }
                    elseif(is_iterable($v))
                    {
                        $in = iterator_to_array($v);
                    }
                    elseif($v instanceof stdClass)
                    {
                        $in = (array)$v;
                    }

                    $args = self::map(function($v, $k) use($in) {
                        return $in[$k];
                    }, $params);
                    return $type->newInstanceArgs($args);
                }, $iterable);
            }
        });
        return $mapTo(...$args);
    }
}
