<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use FPHP\collection\Adjust;
use FPHP\collection\Append;
use FPHP\collection\Assoc;
use FPHP\collection\Concat;
use FPHP\collection\Emptied;
use FPHP\collection\Filter;
use FPHP\collection\HasProp;
use FPHP\collection\IndexBy;
use FPHP\collection\InTo;
use FPHP\collection\Keys;
use FPHP\collection\Map;
use FPHP\collection\Merge;
use FPHP\collection\Pick;
use FPHP\collection\PickAll;
use FPHP\collection\Pluck;
use FPHP\collection\Prop;
use FPHP\collection\PropEq;
use FPHP\collection\Props;
use FPHP\collection\Reject;
use FPHP\collection\Take;
use FPHP\collection\TakeWhile;
use FPHP\collection\Values;
use FPHP\utilities\IterableGenerator;

class FPHP
{
    use Reducing;
    use Logical;
    use Functions;
    use Predicates;
    use Relational;
    use Additional;

    use Add;
    use Adjust;
    use Append;
    use Assoc;
    use Concat;
    use Dec;
    use Emptied;
    use Equals;
    use Filter;
    use HasProp;
    use Inc;
    use IndexBy;
    use InTo;
    use Keys;
    use Map;
    use Memoize;
    use Merge;
    use Pick;
    use PickAll;
    use Pluck;
    use Prop;
    use PropEq;
    use Props;
    use Reject;
    use Take;
    use TakeWhile;
    use Values;

    public static function flatten(...$args)
    {
        $flatten = self::curry(function(iterable $target) {
            if(method_exists($target, "flatten"))
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
                $iterable = self::generatorToIterable($generator);
                if(is_array($target))
                {
                    return iterator_to_array($iterable, false);
                }
                else
                {
                    return $iterable;
                }
            }
        });
        return $flatten(...$args);
    }

    public static function walk(...$args)
    {
        $walk = self::curry(function(callable $func, iterable $iterable) {
            if(method_exists($iterable, "walk"))
            {
                return $iterable->walk($func);
            }
            else
            {
                $generator = function() use($iterable, $func) {
                    foreach($iterable as $k => $v)
                    {
                        $func($v, $k);
                        yield $k => $v;
                    }
                };
                return self::generatorToIterable($generator);
            }
        });
        return $walk(...$args);
    }

    public static function collect(...$args)
    {
        $collect = self::curry(function($iterable){
            if(is_array($iterable))
            {
                return $iterable;
            }
            else
            {
                // implicit TRUE means repeated keys get overridden
                // but FALSE would mean keys not returned
                return iterator_to_array($iterable);
            }
        });
        return $collect(...$args);
    }

    public static function generatorToIterable($generator)
    {
        return new IterableGenerator($generator);
    }
}
