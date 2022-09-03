<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use FPHP\utilities\IterableGenerator;

trait Flatten
{
    public static function flatMap(callable $fn, iterable $target)
    {
        if(is_object($target) && method_exists($target, "flatMap"))
        {
            return $target->flatMap($fn);
        }
        else
        {
            $fnFlatMap = F::pipe(
                fn($coll) => self::map($fn, $coll),
                fn($coll) => self::flatten($coll)
            );
            return $fnFlatMap($target);
        }
    }

    public static function flatten(iterable $target)
    {
        if(is_object($target) && method_exists($target, "flatten"))
        {
            return $target->flatten();
        }
        else
        {
            $generator = function() use($target) {
                foreach($target as $v)
                {
                    if(is_iterable($v))
                    {
                        yield from $v;
                    }
                    else
                    {
                        yield $v;
                    }
                }
            };
            $iterable = new IterableGenerator($generator);
            if(is_array($target))
            {
                return iterator_to_array($iterable, false);
            }
            else
            {
                return $iterable;
            }
        }
    }
}