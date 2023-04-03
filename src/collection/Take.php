<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Take
{
    public static function takeT(int $count, callable $step)
    {
        $i = 0;
        return function($acc, $v, $k) use($step, $count, &$i) {
            $i++;
            if($i < $count)
            {
                return $step($acc, $v, $k);
            }
            else if($i === $count)
            {
                return new Reduced($step($acc, $v, $k));
            }
            else
            {
                return new Reduced($acc);
            }
        };
    }

    public static function take(int $count, $target)
    {
        if(is_object($target) && method_exists($target, "take"))
        {
            return $target->take($count);
        }
        else
        {
            // step preserves keys, so use K step
            return self::transduce(
                fn($step) => self::takeT($count, $step),
                self::defaultStepK($target),
                self::emptied($target),
                $target
            );
        }
    }
}