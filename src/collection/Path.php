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
        $assocPath = self::curry(function(iterable $path, $val, $target)
        {
            $pathArr = is_array($path) ? $path : iterator_to_array($path, false);

            // iteratively call assoc, starting at the deepest level, against the depest
            // entry in the path
            // i.e. trying to build something like this -
            //        self::pipe(
            //            self::assoc("uniform", self::__(), self::path(["jobs", 0], $target)),
            //            self::assoc(0, self::__(), self::path(["jobs"], $target)),
            //            self::assoc("jobs", self::__(), $target)
            //        );

            $targets = self::reduce(function($acc, $prop) {
                return array_merge($acc, [self::prop($prop, $acc[array_key_last($acc)])]);
            }, [$target], $pathArr);

            $assocs = array_map(function($prop, $target) {
                return self::assoc($target, self::__(), $prop);
            }, $pathArr, array_slice($targets, 0, -1));

            $fn = self::pipe(...array_reverse($assocs));

            // we call the assoc chain now, kicking it off with the new value for
            // the deepest level in the path. this will produce the value which will
            // be passed into the next deepest level in the path and so forth

            return $fn($val);
        });
        return $assocPath(...$args);
    }
}