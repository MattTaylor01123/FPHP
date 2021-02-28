<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use IteratorAggregate;
use JsonSerializable;
use Traversable;

class RamdaPHP
{
    use Mapping;
    use Filtering;
    use Reducing;
    use Logical;
    use Dictionaries;
    use Functions;
    use Predicates;
    use Relational;
    use Additional;

    use Add;
    use Adjust;
    use Append;
    use Appendk;
    use Assoc;    
    use Concat;
    use Equals;
    use InTo;
    use Memoize;
    use PickAll;
    use PropEq;

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

    public static function merge(iterable ...$args)
    {
        return self::generatorToIterable(function() use($args) {
            foreach($args as $iterable)
            {
                yield from $iterable;
            }
        });
    }

    protected static function transformTraversable($transducer, $traversable)
    {
        return new class($transducer, $traversable) implements \IteratorAggregate
        {
            private $transducer = null;
            private $traversable = null;
            public function __construct($transducer, $traversable)
            {
                $this->transducer = $transducer;
                $this->traversable = $traversable;
            }
            public function getIterator(): Traversable
            {
                $curr = null;
                $key = null;
                $set = false;
                $step = function($acc, $v, $k) use(&$curr, &$key, &$set) {
                    $curr = $v;
                    $key = $k;
                    $set = true;
                };
                $transducer = $this->transducer;
                $reducer = $transducer($step);
                foreach($this->traversable as $k => $v)
                {
                    $set = false;
                    $reducer(null, $v, $k);
                    if($set)
                    {
                        yield $key => $curr;
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
                $out = RamdaPHP::pipex(
                    iterator_to_array($this->getIterator(), true),
                    fn($a) => RamdaPHP::isSequentialArray($a) ? RamdaPHP::values($a) : $a
                );
                return $out;
            }
        };
    }
}
