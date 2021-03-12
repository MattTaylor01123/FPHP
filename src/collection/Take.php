<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

final class Take
{
    // TODO - return type should depend on the input type. Should consider
    // implementing early terminating reducer functionality.
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