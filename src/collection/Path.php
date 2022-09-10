<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Path
{
    public static function path(iterable $path, $target)
    {
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
    }

    public static function assocPath(iterable $path, $val, $target)
    {
        return self::ssocPath($path, $val, $target, fn($acc, $v, $k) => self::assoc($acc, $v, $k));
    }

    public static function dissocPath(iterable $path, $val, $target)
    {
        return self::ssocPath($path, $val, $target, fn($acc, $p) => self::dissoc($acc, $p));
    }

    private static function ssocPath(iterable $path, $val, $target, $step)
    {
        $pathArr = is_array($path) ? $path : iterator_to_array($path, false);
        $pathLen = count($pathArr);

        if($pathLen === 0)
        {
            throw new InvalidArgumentException("Invalid path length");
        }
        else if($pathLen === 1)
        {
            return $step($target, $val, $path[0]);
        }
        else if(self::isTraversable($target) || self::isGenerator($target))
        {
            $fn = function() use($pathArr, $val, $target, $pathLen, $step) {
                $returnedVal = false;
                foreach($target as $k => $v)
                {
                    if($k === $pathArr[0] && $pathLen > 1)
                    {
                        $returnedVal = true;
                        yield $k => self::ssocPath(array_slice($pathArr, 1), $val, $v, $step);
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
                $newV = self::ssocPath(array_slice($pathArr, 1), $val, $currV, $step);
                $out = $step($target, $newV, $pathArr[0]);
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
    }
}