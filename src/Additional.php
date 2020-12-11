<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use ReflectionClass;
use stdClass;

trait Additional
{
    public static function columns(...$args)
    {
        $columns = self::curry(function(array $properties, iterable $iterable) {
            if(method_exists($iterable, "columns"))
            {
                $out = $iterable->columns($properties);
            }
            else
            {
                $out = self::map(self::pick($properties), $iterable);
            }
            return $out;
        });
        return $columns(...$args);
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