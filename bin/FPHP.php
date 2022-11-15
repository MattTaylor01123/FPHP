<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use ArrayIterator;
use Closure;
use Exception;
use InvalidArgumentException;
use ReflectionFunction;
use src\collection\Reduced;
use src\utilities\IterableGenerator;
use src\utilities\TransformedTraversable;
use stdClass;
use Traversable;

final class FPHP
{
        public static function adjustT($idx, callable $transform, callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
    }

    public static function adjust($idx, callable $transform, $list) {
        return self::transduce(
            fn($step) => self::adjustT($idx, $transform, $step),
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Adjust preserves keys anyway, so using assoc is fine.
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($list),
            $list
        );
    }

    public static function append($acc, $val)
    {
        if(is_array($acc))
        {
            $out = $acc;
            $out[] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $acc) {
                yield from $acc;
                yield $val;
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }

    public static function assoc($acc, $val, $propName)
    {
        if(is_array($acc))
        {
            $out = $acc;
            $out[$propName] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $returnedVal = false;
            $fn = function() use($propName, $val, $acc, &$returnedVal) {
                foreach($acc as $k => $v)
                {
                    if($k === $propName)
                    {
                        $returnedVal = true;
                        yield $k => $val;
                    }
                    else
                    {
                        yield $k => $v;
                    }
                }
                if(!$returnedVal)
                {
                    $returnedVal = true;
                    yield $propName => $val;
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_object($acc))
        {
            $out = clone $acc;
            $out->$propName = $val;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
        }
        return $out;
    }

    public static function concat($v1, $v2)
    {
        $v1t = gettype($v1);
        $v2t = gettype($v2);
        $v1type = $v1t === "object" ? get_class($v1) : $v1t;
        $v2type = $v2t === "object" ? get_class($v2) : $v2t;

        if($v1type !== $v2type)
        {
            throw new InvalidArgumentException("v1 and v2 must be of the same type");
        }

        if(is_object($v1) && method_exists($v1, "concat"))
        {
            $out = $v1->concat($v2);
        }
        else if(is_string($v1) && is_string($v2))
        {
            $out = $v1.$v2;
        }
        else if(is_array($v1) && is_array($v2))
        {
            $out = array_merge(array_values($v1), array_values($v2));
        }
        else if($v1 instanceof Traversable && $v2 instanceof Traversable)
        {
            $fn = function() use($v1, $v2) {
                yield from $v1;
                yield from $v2;
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("v1 and v2 of unhandled type");
        }
        return $out;
    }

    public static function dissoc($acc, $propName)
    {
        if(is_array($acc))
        {
            $out = $acc;
            unset($out[$propName]);
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($propName, $acc) {
                foreach($acc as $k => $v)
                {
                    if($k !== $propName)
                    {
                        yield $k => $v;
                    }
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_object($acc))
        {
            $out = clone $acc;
            unset($out->$propName);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
        }
        return $out;
    }

    public static function emptied($v)
    {
        if(is_array($v))
        {
            return [];
        }
        if(is_iterable($v))
        {
            $fn = function() {
                yield from [];
            };
            $init = self::generatorToIterable($fn);
            return $init;
        }
        if(is_string($v))
        {
            return "";
        }
        if(is_int($v))
        {
            return 0;
        }
        if(is_bool($v))
        {
            return false;
        }
        if(is_float($v))
        {
            return 0.0;
        }
        $class = get_class($v);
        if($class !== false)
        {
            return new $class();
        }
        throw new Exception("Unable to find a suitable empty value for the type of 'v'");
    }

    public static function filterT(callable $predicate, callable $step) : callable
    {
        return fn($acc, $v, $k) => ($predicate($v, $k) ? $step($acc, $v, $k) : $acc);
    }

    public static function filterK(callable $predicate, mixed $target) : mixed
    {
        if (is_array($target))
        {
            $out = array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH );
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            // transduce but passing assoc as step function, so that key is preserved
            $out = self::transduce(
                fn($step) => self::filterT($predicate, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }

    public static function filter(callable $predicate, mixed $target) : mixed
    {
        if(is_array($target))
        {
            $out = array_values(array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH));
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            $notTravOrGen = !($target instanceof \Traversable || self::isGenerator($target));
            // use the transduce filter, but ignore key
            $out = self::transduce(
                fn($step) => self::filterT($predicate, $step),
                fn($acc, $v) => self::append($acc, $v),
                $notTravOrGen ? [] : self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException(
                "'target' must be one of array, traversable, object, or generator"
            );
        }
        return $out;
    }

    public static function find(callable $predicate, iterable $iterable) : mixed
    {
        if(is_object($iterable) && method_exists($iterable, "find"))
        {
            return $iterable->find($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : null, null, $iterable);
        }
    }

    public static function findIndex(callable $predicate, iterable $iterable) : int
    {
        if(is_object($iterable) && method_exists($iterable, "findIndex"))
        {
            return $iterable->findIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : -1, -1, $iterable);
        }
    }

    public static function first($target)
    {
        if(is_object($target) && method_exists($target, "first"))
        {
            return $target->first();
        }
        else if(is_iterable($target))
        {
            $out = null;
            foreach($target as $v)
            {
                $out = $v;
                break;
            }
            return $out;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
    }

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

    public static function groupBy(callable $fnGroup, iterable $input) : array
    {
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $input
        );
    }

    public static function groupMapBy(callable $fnGroup, callable $fnMap, iterable $input) : array
    {
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $input
        );
    }

    public static function groupReduceBy(callable $fnGroup, callable $fnReduce, mixed $initial, iterable $input) : array
    {
        $out = array();
        foreach($input as $k => $v)
        {
            $g = $fnGroup($v, $k);
            if($g === null)
            {
                continue;
            }
            if(!array_key_exists($g, $out))
            {
                $out[$g] = self::emptied($initial);
            }
            $out[$g] = $fnReduce($out[$g], $v, $k);
        }
        return $out;
    }

    public static function hasProp(string $propName, $target)
    {
        return ((is_object($target) && property_exists($target, $propName)) ||
                (is_array($target) && key_exists($propName, $target)));
    }

    public static function hasProps(array $propNames, $target)
    {
        return self::all(fn($p) => self::hasProp($p, $target), $propNames);
    }

    public static function indexByT(callable $func, callable $step) {
        return fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
    }

    public static function indexBy(callable $func, $coll)
    {
        if(is_object($coll) && method_exists($coll, "indexBy"))
        {
            $out = $coll->indexBy($func);
        }
        else if(is_array($coll) || $coll instanceof Traversable)
        {
            $out = self::transduce(
                fn($step) => self::indexByT($func, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($coll),
                $coll
            );
        }
        else
        {
            throw new InvalidArgumentException("unrecognised iterable");
        }
        return $out;
    }

    public static function indexOf($needle, $target)
    {
        if(is_object($target) && method_exists($target, "indexOf"))
        {
            return $target->indexOf();
        }
        elseif(is_array($target))
        {
            return array_search($needle, $target, true) ?: -1;
        }
        elseif(is_iterable($target))
        {
            return self::reduce(fn($acc, $v, $k) => $v === $needle ? new Reduced($k) : -1, -1, $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must have method 'indexOf' or be an iterable.");
        }
    }

    public static function inTo($initial, callable $transducer, $collection)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $collection);
    }

    public static function inToAssoc($initial, callable $transducer, $collection)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::assoc($acc, $v, $k), $initial, $collection);
    }

    public static function iterableToArray(iterable $it)
    {
        $entries = array();
        $hasKeys = false;
        foreach($it as $k => $v)
        {
            $entries[] = [$v, $k];
            $hasKeys = $hasKeys || ($k !== 0);
        }

        $step = $hasKeys ? fn($acc, $v, $k) => self::assoc($acc, $v, $k)
                         : fn($acc, $v) => self::append($acc, $v);
        $out = self::reduce(fn($acc, $v) => $step($acc, $v[0], $v[1]), [], $entries);
        return $out;
    }

    public static function keysT(callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $k, 0);
    }

    public static function keys($target)
    {
        $transduceInto = fn($initial) => self::transduce(
            fn($step) => self::keysT($step),
            fn($acc, $v) => self::append($acc, $v),
            $initial,
            $target
        );
        if(is_object($target) && method_exists($target, "keys"))
        {
            $out = $target->keys();
        }
        else if(is_array($target))
        {
            $out = array_keys($target);
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
    }

    public static function mapT(callable $func, callable $step)
    {
        return fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
    }

    public static function map(callable $func, $coll)
    {
        if(is_object($coll) && method_exists($coll, "map"))
        {
            $out = $coll->map($func);
        }
        // array_map callback doesn't support keys
        // always use "assoc" for step function as we can't tell if a traversable is
        // associative or not without iterating it, and we can't do that in case it
        // is infinite. Map preserves keys anyway, so using assoc is fine.
        else if( is_object($coll) || is_array($coll) || self::isTraversable($coll) || self::isGenerator($coll))
        {
            $out = self::transduce(
                fn($step) => self::mapT($func, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($coll),
                $coll
            );
        }
        else
        {
            throw new InvalidArgumentException("target must be one of array, stdClass, generator, functor.");
        }
        return $out;
    }

    public static function matchT($criteria, $step)
    {
        return self::filterT(function($v) use($criteria) {
            return self::all(function($func, $field) use($v) {
                return $func(self::prop($field, $v));
            }, $criteria);
        }, $step);
    }

    public static function match(iterable $criteria, iterable $target)
    {
        if(is_object($target) && method_exists($target, "match"))
        {
            $out = $target->match($criteria);
        }
        else if(is_array($target) || is_object($target) || self::isTraversable($target) || self::isGenerator($target))
        {
            $out = self::transduce(
                fn($step) => self::matchT($criteria, $step),
                self::defaultStep($target),
                self::emptied($target),
                $target
            );
        }
        else
        {
            throw new InvalidArgumentException("target must be one of array, stdClass, generator, functor.");
        }
        return $out;
    }

    public static function merge($v1, $v2)
    {
        $v1t = gettype($v1);
        $v2t = gettype($v2);
        $v1type = $v1t === "object" ? get_class($v1) : $v1t;
        $v2type = $v2t === "object" ? get_class($v2) : $v2t;

        if($v1type !== $v2type)
        {
            throw new InvalidArgumentException("v1 and v2 must be of the same type");
        }

        if(is_object($v1) && method_exists($v1, "merge"))
        {
            $out = $v1->merge($v2);
        }
        else if(is_array($v1))
        {
            $out = array_merge($v1, $v2);
        }
        else if(is_object($v1))
        {
            $out = self::reduce(function($acc, $v) {
                return self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $v);
            }, self::emptied($v1), [$v1, $v2]);
        }
        else
        {
            throw new InvalidArgumentException("v1 and v2 of unhandled type");
        }
        return $out;
    }

    public static function nth(...$args)
    {
        $nth = self::curry(function(int $n, $target) {
            $isArr = is_array($target);
            if($n >= 0 && !$isArr)
            {
                $acc = 0;
                $out = null;
                foreach($target as $v)
                {
                    if($acc === $n)
                    {
                        $out = $v;
                        break;
                    }
                }
            }
            else
            {
                $vals = $isArr ? $target : iterator_to_array($target, false);
                $len = count($vals);
                if($len === 0 || $n > $len || $len + $n < 0)
                {
                    $out = null;
                }
                else
                {
                    $idx = ($n > 0 ? $n : $len + $n);
                    $key = array_keys($vals)[$idx];
                    $out = $vals[$key];
                }
            }
            return $out;
        });
    }

    public static function partitionByT(callable $fnGroup, callable $step)
    {
        return self::partitionReduceByT(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $step
        );
    }

    public static function partitionMapByT(callable $fnGroup, callable $fnMap, callable $step)
    {
        return self::partitionReduceByT(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $step
        );
    }

    public static function partitionReduceByT(callable $fnGroup, callable $fnReduce, $initial, callable $step)
    {
        $started = false;
        $grp = null;
        $cache = null;
        return function ($acc, $v, $k) use($fnGroup, $step, &$grp, &$cache, &$started, $fnReduce, $initial) {
            $currGrp = $fnGroup($v, $k);
            if(!$started)
            {
                $started = true;
                $grp = $currGrp;
                $cache = $fnReduce(self::emptied($initial), $v, $k);
                $out = $acc;
            }
            else if($currGrp !== $grp)
            {
                $out = $step($acc, $cache, $grp);
                $cache = $fnReduce(self::emptied($initial), $v, $k);
                $grp = $currGrp;
            }
            else
            {
                $cache = $fnReduce($cache, $v, $k);
                $out = $acc;
            }
            return $out;
        };
    }

    public static function partitionBy(callable $fnGroup, iterable $target)
    {
        return self::transduce(
            self::partitionByT($fnGroup),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }

    public static function partitionReduceBy(callable $fnGroup, callable $fnReducer, $initial, iterable $target)
    {
        return self::transduce(
            fn($step) => self::partitionReduceByT($fnGroup, $fnReducer, $initial, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }

    public static function partitionMapBy(callable $fnGroup, callable $fnMap, iterable $target)
    {
        return self::transduce(
            fn($step) => self::partitionMapByT($fnGroup, $fnMap, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($target),
            $target
        );
    }

    public static function path(iterable $path, $target)
    {
        return self::reduce(function($acc, $part) {
            if($acc)
            {
                return self::prop($part, $acc);
            }
            else
            {
                return new Reduced($acc);
            }
        }, $target, $path);
    }

    public static function assocPath(iterable $path, $val, $target)
    {
        return self::ssocPath($path, $val, $target, fn($acc, $v, $k) => self::assoc($acc, $v, $k));
    }

    public static function dissocPath(iterable $path, $val, $target)
    {
        return self::ssocPath($path, $val, $target, fn($acc, $p) => self::dissoc($acc, $p));
    }

    private static function ssocPath(iterable $path, $val, $target, $step)
    {
        $pathArr = is_array($path) ? $path : iterator_to_array($path, false);
        $pathLen = count($pathArr);

        if($pathLen === 0)
        {
            throw new InvalidArgumentException("Invalid path length");
        }
        else if($pathLen === 1)
        {
            return $step($target, $val, $path[0]);
        }
        else if(self::isTraversable($target) || self::isGenerator($target))
        {
            $fn = function() use($pathArr, $val, $target, $pathLen, $step) {
                $returnedVal = false;
                foreach($target as $k => $v)
                {
                    if($k === $pathArr[0] && $pathLen > 1)
                    {
                        $returnedVal = true;
                        yield $k => self::ssocPath(array_slice($pathArr, 1), $val, $v, $step);
                    }
                    else
                    {
                        yield $k => $v;
                    }
                }
                if(!$returnedVal)
                {
                    throw new Exception("Invalid path");
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_array($target) || is_object($target))
        {
            if(self::hasProp($pathArr[0], $target))
            {
                $currV = self::prop($path[0], $target);
                $newV = self::ssocPath(array_slice($pathArr, 1), $val, $currV, $step);
                $out = $step($target, $newV, $pathArr[0]);
            }
            else
            {
                throw new Exception("Invalid path");
            }
        }
        else
        {
            throw new InvalidArgumentException("'target' must be of type array, traversable, generator, or object");
        }
        return $out;
    }

    public static function pick(iterable $properties, $target)
    {
        return self::filterK(fn($v, $k) => self::includes($k, $properties), $target);
    }

    public static function pluck(string $propName, iterable $iterable)
    {
        if(is_object($iterable) && method_exists($iterable, "pluck"))
        {
            $out = $iterable->pluck($propName);
        }
        else if(is_array($iterable))
        {
            $out = array_column($iterable, $propName);
        }
        else if($iterable instanceof Traversable)
        {
            $out = self::map(fn($o) => self::prop($propName, $o), $iterable);
        }
        else
        {
            throw new InvalidArgumentException(
                "unrecognised iterable"
            );
        }
        return $out;
    }

    public static function project(array $properties, iterable $iterable)
    {
        if(is_object($iterable) && method_exists($iterable, "project"))
        {
            $out = $iterable->project($properties);
        }
        else
        {
            $out = self::map(fn($v) => self::pick($properties, $v), $iterable);
        }
        return $out;
    }

    public static function prop(string $propName, $target)
    {
        if(is_array($target))
        {
            $out = $target[$propName] ?? null;
        }
        else if(is_object($target) && method_exists($target, "prop"))
        {
            $out = $target->prop($propName);
        }
        else if(is_object($target) && method_exists($target, "get"))
        {
            $out = $target->get($propName);
        }
        else if($target instanceof \Traversable)
        {
            $out = null;
            foreach($target as $k => $v)
            {
                if($k === $propName)
                {
                    $out = $v;
                    break;
                }
            }
        }
        else if(is_object($target))
        {
            $out = $target->$propName ?? null;
        }
        else
        {
            throw new InvalidArgumentException("Invalid type for 'target'");
        }
        return $out;
    }

    public static function propEq(string $propName, $val, $target)
    {
        return self::hasProp($propName, $target) &&
               self::eq(self::prop($propName, $target), $val);
    }

    public static function props(array $properties, $target)
    {
        $out = array();
        foreach($properties as $prop)
        {
            if(self::hasProp($prop, $target))
            {
                $out[] = self::prop($prop, $target);
            }
            else
            {
                $out[] = null;
            }
        }
        return $out;
    }

    public static function reject(callable $func, iterable $target)
    {
        return self::filter(self::complement($func), $target);
    }

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
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Take preserves keys anyway, so using assoc is fine.
            return self::transduce(
                fn($step) => self::takeT($count, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
    }

    public static function takeWhileT(callable $pred, callable $step)
    {
        $fin = false;
        return function($acc, $v, $k) use(&$fin, $pred, $step) {
            $fin = $fin || !$pred($v, $k);
            if(!$fin)
            {
                return $step($acc, $v, $k);
            }
            else
            {
                return new Reduced($acc);
            }
        };
    }

    public static function takeWhile(callable $pred, $target)
    {
        if(is_object($target) && method_exists($target, "takeWhile"))
        {
            return $target->takeWhile($pred);
        }
        else
        {
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Take preserves keys anyway, so using assoc is fine.
            return self::transduce(
                fn($step) => self::takeWhileT($pred, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($target),
                $target
            );
        }
    }

    public static function transduce(callable $transducer, callable $step, $initial, $collection)
    {
        if($initial instanceof Traversable)
        {
            return new TransformedTraversable($transducer, $step, $collection);
        }
        else
        {
            return self::reduce($transducer($step), $initial, $collection);
        }
    }

    public static function valuesT(callable $step)
    {
        return function($acc, $v) use($step) {
            return $step($acc, $v);
        };
    }

    public static function values($target)
    {
        $transduceInto = fn($initial, $target) => self::transduce(
            fn($step) => self::valuesT($step),
            fn($acc, $v) => self::append($acc, $v),
            $initial,
            $target
        );
        if(is_object($target) && method_exists($target, "values"))
        {
            $out = $target->values();
        }
        else if(is_array($target))
        {
            $out = array_values($target);
        }
        else if(is_iterable($target))
        {
            $out = $transduceInto(self::emptied($target), $target);
        }
        else if(is_object($target))
        {
            $out = $transduceInto(self::emptied([]), $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must be iterable or object");
        }
        return $out;
    }

    public static function all(callable $fnPred, iterable $iterable) : bool
    {
        if(is_object($iterable) && method_exists($iterable, "all"))
        {
            return $iterable->all($fnPred);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) =>
                (!$fnPred($v, $k) ? new Reduced(false) : true), true, $iterable);
        }
    }

    public static function allPass(callable ...$args)
    {
        return fn($v) => self::all(fn($fn) => $fn($v), $args);
    }

    public static function any(callable $fnPred, iterable $iterable) : bool
    {
        if(is_object($iterable) && method_exists($iterable, "any"))
        {
            return $iterable->any($fnPred);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) =>
                ($fnPred($v, $k) ? new Reduced(true) : false), false, $iterable);
        }
    }

    public static function anyPass(callable ...$args)
    {
        return fn($v) => self::any(fn($fn) => $fn($v), $args);
    }

    public static function eq($v1, $v2)
    {
        if(is_object($v2) && method_exists($v2, "eq"))
        {
            return $v2->eq($v1);
        }
        if(is_object($v2) && method_exists($v2, "equals"))
        {
            return $v2->equals($v1);
        }
        if(is_object($v1) && method_exists($v1, "eq"))
        {
            return $v1->eq($v2);
        }
        if(is_object($v1) && method_exists($v1, "equals"))
        {
            return $v1->equals($v2);
        }

        if($v1 === $v2)
        {
            return true;
        }
        $t1 = gettype($v1);
        $t2 = gettype($v2);
        if($t1 !== $t2)
        {
            return false;
        }
        if(self::isIterable($v1) || $v1 instanceof \stdClass)
        {
            foreach($v1 as $k => $v)
            {
                if(!self::propEq($k, $v, $v2))
                {
                    return false;
                }
            }

            foreach($v2 as $k => $v)
            {
                if(!self::propEq($k, $v, $v1))
                {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public static function includes($v, $target)
    {
        if(is_object($target) && method_exists($target, "includes"))
        {
            $out = $target->includes($v);
        }
        else if(is_iterable($target))
        {
            $out = self::reduce(fn($acc, $v2) => $v === $v2 ? new Reduced(true) : false, false, $target);
        }
        else
        {
            throw new InvalidArgumentException("'target' must have method 'includes' or be iterable.");
        }
        return $out;
    }

    public static function includesAll(iterable $vals, iterable $list)
    {
        return self::all(fn($v) => self::includes($v, $list), $vals);
    }

    public static function includesAny(iterable $vals, iterable $list)
    {
        return self::any(fn($v) => self::includes($v, $list), $vals);
    }

    public static function curry(Callable $func)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();
        return self::curryN($func, $arity, false);
    }

    public static function curryN(Callable $func, int $arity, bool $reverseParams, ...$capturedArgs)
    {
        $outFunc = function(...$args) use($func, $arity, $reverseParams, $capturedArgs)
        {
            $orderedArgs = $reverseParams ? array_reverse($args) : $args;
            $itArgs = new ArrayIterator($orderedArgs);
            $new = array();
            foreach($capturedArgs as $curr)
            {
                if($curr === self::__() && $itArgs->valid())
                {
                    $new[] = $itArgs->current();
                    $itArgs->next();
                }
                else
                {
                    $new[] = $curr;
                }
            }
            while($itArgs->valid())
            {
                $new[] = $itArgs->current();
                $itArgs->next();
            }
            $noPlaceholders = array_filter($new, function($e) {
                return $e !== self::__();
            });

            if(count($noPlaceholders) >= $arity)
            {
                return $func(...array_slice($noPlaceholders, 0, $arity));
            }
            else
            {
                return self::curryN($func, $arity, $reverseParams, ...$new);
            }
        };
        return $outFunc;
    }

    public static function __()
    {
        return "__";
    }

    public static function always($v)
    {
        return function() use($v) {
            return $v;
        };
    }

    public static function apply(...$args)
    {
        $apply = self::curry(function(callable $func, array $params) {
            return $func(...$params);
        });
        return $apply(...$args);
    }

    public static function complement(callable $func)
    {
        return function(...$args) use($func) {
            return !$func(...$args);
        };
    }

    public static function flipN(callable $fn, int $arity)
    {
        return self::curryN($fn, $arity, true);
    }

    public static function identity(...$args)
    {
        $identity = self::curry(function($v) {
            return $v;
        });
        return $identity(...$args);
    }

    public static function invoker(...$args)
    {
        $invoker = self::curry(function(int $arity, string $methodName) {
            $func = self::buildFixedArityFunc($arity + 1, function(...$args) use($arity, $methodName) {
                $object = $args[$arity];
                $args = array_slice($args, 0, $arity);
                return $object->$methodName(...$args);
            });
            return self::curry($func);
        });
        return $invoker(...$args);
    }

    public static function partial(Callable $func, ...$args)
    {
        $refFunc = new ReflectionFunction($func);
        $arity = $refFunc->getNumberOfParameters();
        $argCount = count($args);
        $missingArgs = $arity - $argCount;

        return self::buildFixedArityFunc(max(0, $missingArgs), function(...$newArgs) use($args, $func) {
            return $func(...array_merge($args, $newArgs));
        });
    }

    public static function pipe(callable ...$funcs)
    {
        return function(...$args) use($funcs)
        {
            $first = true;
            $out = null;
            foreach($funcs as $fn)
            {
                if($first)
                {
                    $first = false;
                    $out = $fn(...$args);
                }
                else
                {
                    $out = $fn($out);
                }
            }
            return $out;
        };
    }

    public static function pipex($firstParameter, callable ...$funcs)
    {
        $fn = self::pipe(...$funcs);
        return $fn($firstParameter);
    }

    public static function tapT(...$args)
    {
        $tapT = self::curry(function(callable $func, callable $step) {
            return function($acc, $v, $k) use($func, $step) {
                $func($v, $k);
                return $step($acc, $v, $k);
            };
        });
        return $tapT(...$args);
    }

    public static function tap(...$args)
    {
        $tap = self::curry(function(callable $func, $value) {
            return self::transduce(self::tapT($func), self::defaultStep($value), self::emptied($value), $value);
        });

        return $tap(...$args);
    }

    private static function buildFixedArityFunc(int $arity, callable $func)
    {
        switch($arity)
        {
            case 0:
                return function() use($func) { return $func(); };
            case 1:
                return function($a0) use($func) { return $func($a0); };
            case 2:
                return function($a0, $a1) use($func) { return $func($a0, $a1); };
            case 3:
                return function($a0, $a1, $a2) use($func) { return $func($a0, $a1, $a2); };
            case 4:
                return function($a0, $a1, $a2, $a3) use($func) { return $func($a0, $a1, $a2, $a3); };
            case 5:
                return function($a0, $a1, $a2, $a3, $a4) use($func) { return $func($a0, $a1, $a2, $a3, $a4); };
            case 6:
                return function($a0, $a1, $a2, $a3, $a4, $a5) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5); };
            case 7:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6); };
            case 8:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7); };
            case 9:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8); };
            case 10:
                return function($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9) use($func) { return $func($a0, $a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9); };
            default:
                throw new Exception("Arity must be non-negative integer and less than or equal to 10");
        }
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

    public static function memoize(callable $fn)
    {
        return function(...$args) use($fn) {
            static $prev = array();
            $v = self::find(fn($v) => self::propEq(0, $args, $v), $prev);
            if(!$v)
            {
                $out = $fn(...$args);
                $prev[] = [$args, $out];
            }
            else
            {
                $out = $v[1];
            }
            return $out;
        };
    }    

    public static function isArray(...$args)
    {
        $isArray = self::curry(function($v) {
            return is_array($v);
        });
        return $isArray(...$args);
    }

    public static function isBool(...$args)
    {
        $isBool = self::curry(function($v) {
            return is_bool($v);
        });
        return $isBool(...$args);
    }

    public static function isEmpty(...$args)
    {
        $isEmpty = self::curry(function($v) {
            if(self::isString($v))
            {
                return strlen($v) === 0;
            }
            if(is_iterable($v))
            {
                return self::length($v) === 0;
            }
            if(is_object($v))
            {
                return self::length(self::keys($v)) === 0;
            }
            if($v === null)
            {
                return false;
            }
            return false;
        });
        return $isEmpty(...$args);
    }

    public static function isFloat(...$args)
    {
        $isFloat = self::curry(function($v) {
            return is_float($v);
        });
        return $isFloat(...$args);
    }

    public static function isGenerator(...$args)
    {
        $isGenerator = self::curry(function($v) {
            if($v instanceof Generator)
            {
                return true;
            }
            if($v instanceof Closure &&
               (new ReflectionFunction($v))->isGenerator())
            {
                return true;
            }
        });
        return $isGenerator(...$args);
    }

    public static function isInteger(...$args)
    {
        $isInteger = self::curry(function($v) {
            return is_int($v);
        });
        return $isInteger(...$args);
    }

    public static function isIterable(...$args)
    {
        $isGenerator = self::curry(function($arg) {
            return is_iterable($arg);
        });
        return $isGenerator(...$args);
    }

    public static function isObject(...$args)
    {
        $isObject = self::curry(function($v) {
            return is_object($v);
        });
        return $isObject(...$args);
    }

    public static function isSequentialArray($target)
    {
        // https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
        $out = false;
        if(is_array($target) && count($target) > 0 && array_key_exists(0, $target))
        {
            $out = (array_keys($target) === range(0, count($target) - 1));
        }
        return $out;
    }

    public static function isString(...$args)
    {
        $isString = self::curry(function($v) {
            return is_string($v);
        });
        return $isString(...$args);
    }

    public static function isType(...$args)
    {
        $isType = self::curry(function($t, $v) {
            return gettype($v) === $t;
        });
        return $isType(...$args);
    }

    public static function isClass(...$args)
    {
        $isClass = self::curry(function($c, $v) {
            return get_class($v) === $c;
        });
        return $isClass(...$args);
    }

    public static function isStdClass(...$args)
    {
        return self::isA(stdClass::class, ...$args);
    }

    public static function isTraversable(...$args)
    {
        return self::isA(Traversable::class, ...$args);
    }

    public static function isA(...$args)
    {
        $isA = self::curry(function($class, $v) {
            return is_a($v, $class);
        });
        return $isA(...$args);
    }

    public static function isScalarType(...$args)
    {
        $isScalar = self::curry(function($v) {
            return self::includes(gettype($v), ["boolean", "double", "integer", "string"]);
        });
        return $isScalar(...$args);
    }

    public static function isNull(...$args)
    {
        $isNull = self::curry(function($v) {
            return is_null($v);
        });
        return $isNull(...$args);
    }

    public static function test(...$args)
    {
        $test = self::curry(function(string $regex, string $str) {
            return preg_match($regex, $str) === 1;
        });
        return $test(...$args);
    }

    public static function joinUp(...$args)
    {
        $join = self::curry(function($glue, iterable $iterable) {
            if(is_object($iterable) && method_exists($iterable, "joinUp"))
            {
                return $iterable->joinUp($glue, $iterable);
            }
            else
            {
                // exclude keys, we don't need them, and if include them in
                // iterator_to_array call, values with duplicate keys will be
                // overwritten
                $arr = is_array($iterable) ? $iterable : iterator_to_array($iterable, false);
                return implode($glue, $arr);
            }
        });
        return $join(...$args);
    }

    public static function length(...$args)
    {
        $length = self::curry(function(iterable $iterable) {
            if(is_object($iterable) && method_exists($iterable, "length"))
            {
                return $iterable->length();
            }
            elseif(is_array($iterable))
            {
                return count($iterable);
            }
            else
            {
                $out = self::reduce(function($count) {
                    return ++$count;
                }, 0, $iterable);
                return $out;
            }
        });

        return $length(...$args);
    }

    public static function reduce(...$args)
    {
        $reduce = self::curry(function(callable $func, $initial, $iterable) {
            if(is_object($iterable) && method_exists($iterable, "reduce"))
            {
                return $iterable->reduce($func, $initial);
            }
            else
            {
                $out = $initial;
                foreach($iterable as $k => $v)
                {
                    $out = $func($out, $v, $k);
                    if($out instanceof Reduced)
                    {
                        return $out->v;
                    }
                }
                return $out;
            }
        });
        return $reduce(...$args);
    }

}