<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use Exception;
use FPHP\collection\Adjust;
use FPHP\collection\Append;
use FPHP\collection\Assoc;
use FPHP\collection\Concat;
use FPHP\collection\Dissoc;
use FPHP\collection\Emptied;
use FPHP\collection\Filter;
use FPHP\collection\Find;
use FPHP\collection\First;
use FPHP\collection\Flatten;
use FPHP\collection\Group;
use FPHP\collection\HasProp;
use FPHP\collection\HasProps;
use FPHP\collection\IndexBy;
use FPHP\collection\InTo;
use FPHP\collection\IterableToArray;
use FPHP\collection\Keys;
use FPHP\collection\Map;
use FPHP\collection\Matches;
use FPHP\collection\Merge;
use FPHP\collection\Partition;
use FPHP\collection\Path;
use FPHP\collection\Pick;
use FPHP\collection\PickAll;
use FPHP\collection\Pluck;
use FPHP\collection\Project;
use FPHP\collection\Prop;
use FPHP\collection\PropEq;
use FPHP\collection\Props;
use FPHP\collection\Reject;
use FPHP\collection\Take;
use FPHP\collection\TakeWhile;
use FPHP\collection\Values;
use FPHP\logic\All;
use FPHP\logic\Any;
use FPHP\utilities\IterableGenerator;

class FPHP
{
    use Reducing;
    use Functions;
    use Predicates;
    use Relational;

    use Adjust;
    use All;
    use Any;
    use Append;
    use Assoc;
    use Concat;
    use Dissoc;
    use Emptied;
    use Find;
    use Filter;
    use First;
    use Flatten;
    use Group;
    use HasProp;
    use HasProps;
    use IndexBy;
    use InTo;
    use IterableToArray;
    use Keys;
    use Map;
    use Matches;
    use Memoize;
    use Merge;
    use Partition;
    use Path;
    use Pick;
    use PickAll;
    use Pluck;
    use Project;
    use Prop;
    use PropEq;
    use Props;
    use Reject;
    use Take;
    use TakeWhile;
    use Values;

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

    public static function defaultStep($target)
    {
        if(self::isSequentialArray($target) || self::isGenerator($target) || self::isTraversable($target))
        {
            $out = fn($acc, $v) => self::append($acc, $v);
        }
        else if(is_array($target) || is_object($target))
        {
            $out = self::assoc();
        }
        else
        {
            throw new Exception("Not possible to determine a step function for type " . gettype($target));
        }
        return $out;
    }
}
