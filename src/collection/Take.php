<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

final class Take
{
    public static function takeT(...$args)
    {
        $takeT = self::curry(function(int $count, callable $step) {
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
        });
        return $takeT(...$args);
    }

    // TODO - return type should depend on the input type. Should consider
    // implementing early terminating reducer functionality.
    public static function take(...$args)
    {
        $take = self::curry(function(int $count, $target) {
            if(method_exists($target, "take"))
            {
                return $target->take($count);
            }
            else
            {
                return self::transduce(self::takeT($count), self::assoc(), self::emptied($target), $target);
            }
        });
        return $take(...$args);
    }
}