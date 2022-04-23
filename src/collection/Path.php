<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

trait Path
{
    public static function path(...$args)
    {
        $propPath = self::curry(function(iterable $path, $target) {
            return self::reduce(function($acc, $part) {
                if($acc)
                {
                    return self::prop($part, $acc);
                }
                else
                {
                    return new Reduced($acc);
                }
            }, $target, $path);
        });

        return $propPath(...$args);
    }

    public static function assocPath(...$args)
    {
        $assocPath = self::curry(function(iterable $path, $val, $target) {
            $pathArr = is_array($path) ? $path : iterator_to_array($path, false);
            $pathLen = count($pathArr);

            if($pathLen === 0)
            {
                throw new InvalidArgumentException("Invalid path length");
            }
            else if($pathLen === 1)
            {
                return self::assoc($target, $val, $path[0]);
            }
            else if(self::isTraversable($target) || self::isGenerator($target))
            {
                $fn = function() use($pathArr, $val, $target, $pathLen) {
                    $returnedVal = false;
                    foreach($target as $k => $v)
                    {
                        if($k === $pathArr[0] && $pathLen > 1)
                        {
                            $returnedVal = true;
                            yield $k => self::assocPath(array_slice($pathArr, 1), $val, $v);
                        }
                        else
                        {
                            yield $k => $v;
                        }
                    }
                    if(!$returnedVal)
                    {
                        throw new Exception("Invalid path");
                    }
                };
                $out = self::generatorToIterable($fn);
            }
            else if(is_array($target) || is_object($target))
            {
                if(self::hasProp($pathArr[0], $target))
                {
                    $currV = self::prop($path[0], $target);
                    $newV = self::assocPath(array_slice($pathArr, 1), $val, $currV);
                    $out = self::assoc($target, $newV, $pathArr[0]);
                }
                else
                {
                    throw new Exception("Invalid path");
                }
            }
            else
            {
                throw new InvalidArgumentException("'target' must be of type array, traversable, generator, or object");
            }
            return $out;
        });
        return $assocPath(...$args);
    }
}