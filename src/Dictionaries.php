<?php

/* 
 * (c) Matthew Taylor
 */

namespace FPHP;

use stdClass;

trait Dictionaries
{
    public static function hasProp(...$args)
    {
        $hasProp = self::curry(function(string $propName, $target) {
            return ((is_object($target) && property_exists($target, $propName)) ||
                    (is_array($target) && key_exists($propName, $target)));
        });
        return $hasProp(...$args);
    }

    public static function keys(...$args)
    {
        $keys = self::curry(function($target) {
            if(method_exists($target, "keys"))
            {
                return $target->keys();
            }
            else if(is_array($target))
            {
                return array_keys($target);
            }
            else if($target instanceof stdClass)
            {
                return array_keys((array)$target);
            }
            else
            {
                $generator = function() use($target) {
                    foreach($target as $k => $v)
                    {
                        yield $k;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $keys(...$args);
    }

    public static function pick(...$args)
    {
        $pick = self::curry(function(iterable $properties, $target) {
            if(is_object($target))
            {
                $out = new stdClass();
                foreach($properties as $prop)
                {
                    if(self::hasProp($prop, $target))
                    {
                        $out->$prop = self::prop($prop, $target);
                    }
                }
            }
            else if(is_array($target))
            {
                $out = array();
                foreach($properties as $prop)
                {
                    if(self::hasProp($prop, $target))
                    {
                        $out[$prop] = self::prop($prop, $target);
                    }
                }
            }
            else if(is_iterable($target))
            {
                $generator = function() use($target, $properties) {
                    foreach($properties as $p)
                    {
                        $match = self::find(fn($v, $k) => $k === $p, $target);
                        if($match !== null)
                        {
                            yield $p => $match;
                        }
                    }
                };
                $out = self::generatorToIterable($generator);
            }

            return $out;
        });
        return $pick(...$args);
    }

    public static function prop(...$args)
    {
        $prop = self::curry(function($propName, $target) {
            if(is_object($target))
            {
                $out = $target->$propName ?? null;
            }
            else if(is_array($target))
            {
                $out = $target[$propName] ?? null;
            }
            else
            {
                $out = null;
            }
            return $out;
        });
        return $prop(...$args);
    }

    public static function props(...$args)
    {
        $props = self::curry(function(array $properties, $target) {
            $out = array();
            foreach($properties as $prop)
            {
                if(self::hasProp($prop, $target))
                {
                    $out[] = self::prop($prop, $target);
                }
                else
                {
                    $out[] = null;
                }
            }
            return $out;
        });
        return $props(...$args);
    }

    public static function values(...$args)
    {
        $values = self::curry(function($target) {
            if(method_exists($target, "values"))
            {
                return $target->values();
            }
            else if(is_array($target))
            {
                return array_values($target);
            }
            else if($target instanceof stdClass)
            {
                return array_values((array)$target);
            }
            else
            {
                $generator = function() use($target) {
                    foreach($target as $v)
                    {
                        yield $v;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $values(...$args);
    }
}