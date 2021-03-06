<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use stdClass;

trait Values
{
    public static function valuesT(...$args)
    {
        $valuesT = self::curry(function(callable $step) {
            return fn($acc, $v) => $step($acc, $v);
        });
        return $valuesT(...$args);
    }

    public static function values(...$args)
    {
        $values = self::curry(function($target) {
            $transduceInto = self::transduce(self::valuesT(), self::append(), self::__(), $target);
            if(method_exists($target, "values"))
            {
                $out = $target->values();
            }
            else if(is_array($target))
            {
                $out = array_values($target);
            }
            else if(is_iterable($target))
            {
                $out = $transduceInto(self::emptied($target));
            }
            else if(is_object($target))
            {
                $out = $transduceInto(self::emptied([]));
            }
            else
            {
                throw new InvalidArgumentException("'target' must be iterable or object");
            }
            return $out;
        });
        return $values(...$args);
    }
}