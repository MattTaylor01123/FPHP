<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use InvalidArgumentException;
use stdClass;
use Traversable;

trait Filtering
{
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