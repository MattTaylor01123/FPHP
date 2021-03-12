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
use FPHP\collection\Values;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

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

    protected static function transformTraversable($transducer, $step, $traversable)
    {
        return new class($transducer, $step, $traversable) implements \IteratorAggregate
        {
            private $transducer = null;
            private $traversable = null;
            private $step = null;
            public function __construct($transducer, $step, $traversable)
            {
                $this->transducer = $transducer;
                $this->step = $step;
                $this->traversable = $traversable;
            }
            public function getIterator(): Traversable
            {
                $curr = null;
                $set = false;
                $step = $this->step;
                
                $stepWrapper = function(...$args) use(&$curr, &$step, &$set) {
                    $acc = fn() => yield from [];
                    $curr = $step($acc(), ...array_slice($args, 1));
                    $set = true;
                };

                $initial = function() { yield 7; };

                $transducer = $this->transducer;
                $reducer = $transducer($stepWrapper);
                foreach($this->traversable as $k => $v)
                {
                    $set = false;
                    $reducer($initial, $v, $k);
                    if($set)
                    {
                        yield from $curr;
                    }
                }
            }
        };
    }

    protected static function generatorToIterable($generator)
    {
        return new class($generator) implements IteratorAggregate, JsonSerializable {

            private $generator;

            public function __construct($generator)
            {
                $this->generator = $generator;
            }

            public function getIterator(): Traversable
            {
                $fn = $this->generator;
                return $fn();
            }

            public function jsonSerialize()
            {
                $out = FPHP::pipex(
                    iterator_to_array($this->getIterator(), true),
                    fn($a) => FPHP::isSequentialArray($a) ? FPHP::values($a) : $a
                );
                return $out;
            }
        };
    }
}
