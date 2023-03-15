<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use ArrayIterator;
use Closure;
use Exception;
use InvalidArgumentException;
use ArgumentCountError;
use ReflectionFunction;
use src\collection\Reduced;
use src\utilities\IterableGenerator;
use src\utilities\TransformedTraversable;
use stdClass;
use Traversable;

final class FPHP
{
    /**
     * Creates a transducer function.
     *
     * Every value passed into the transducer function is passed to the step
     * function, except that the value at the given index is transformed using
     * the given transformation function before being passed to the step function.
     *
     * @param mixed $idx            index
     * @param callable $transform   transformation function
     * @param callable $step        step function
     *
     * @return callable transducer
     */
    public static function adjustT($idx, callable $transform, callable $step) : callable
    {
        return fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
    }

    public static function adjust($idx, callable $transform, $collection = null)
    {
        if($collection === null)
        {
            return fn($collection) => self::adjust($idx, $transform, $collection);
        }
        return self::transduce(
            fn($step) => self::adjustT($idx, $transform, $step),
            // always use "assoc" for step function as we can't tell if a traversable is
            // associative or not without iterating it, and we can't do that in case it
            // is infinite. Adjust preserves keys anyway, so using assoc is fine.
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
        );
    }

    /**
     * Creates a new keyed collection which contains all the values from the input
     * collection and then the passed in key => value pair appended to the end.
     *
     * Regardless of acc's type, the returned value will always be a lazy
     * Traversable. Otherwise, for arrays, if the key already existed in the array
     * then the new value would overwrite the old value rather than being appended
     * to the end.
     *
     * I.e. keys are not guaranteed to be unique in the returned Traversable.
     *
     * @param iterable $acc     input collection
     * @param mixed $val        value to append to end of collection
     * @param mixed $key        key to associate with value
     *
     * @return Traversable new collection
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function appendK(iterable $acc, $val, $key) : Traversable
    {
        if(is_array($acc) || self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $key, $acc) {
                yield from $acc;
                yield $key => $val;
            };
            $out = self::generatorToIterable($fn);
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array or traversable");
        }
        return $out;
    }

    /**
     * Creates a new un-keyed collection which contains all the values from the
     * input collection and then the passed in value appended as the last value
     * in the new collection.
     *
     * @param iterable $acc    input collection
     * @param mixed $val       value to append to end of new collection
     *
     * @return iterable new collection
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function append(iterable $acc, $val) : iterable
    {
        if(is_array($acc))
        {
            $out = array_values($acc);
            $out[] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $acc) {
                // don't yield from as not preserving keys
                foreach($acc as $v)
                {
                    yield $v;
                }
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

    /**
     * Returns a new keyed collection containing all the values in the input
     * collection plus the value passed in as the final value at the end of the
     * collection, keyed with the key value passed in
     *
     * @param array|iterable|object $acc    input collection
     * @param mixed $val                    value to append at end of new collection
     * @param mixed $key                    key to use when appending value to end of new collection
     *
     * @return array|iterable|object    new collection
     *
     * @throws InvalidArgumentException if input collection is not of type array, iterable, or
     * object.
     */
    public static function assoc($acc, $val, $key)
    {
        if(is_array($acc))
        {
            $out = $acc;
            $out[$key] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $returnedVal = false;
            $fn = function() use($key, $val, $acc, &$returnedVal) {
                foreach($acc as $k => $v)
                {
                    if($k === $key)
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
                    yield $key => $val;
                }
            };
            $out = self::generatorToIterable($fn);
        }
        else if(is_object($acc))
        {
            $out = clone $acc;
            $out->$key = $val;
        }
        else
        {
            throw new InvalidArgumentException("'acc' must be of type array, traversable, or object");
        }
        return $out;
    }

    /**
     * Joins all the input collections together to form a new output collection.
     *
     * If all input collections are arrays, then the output collection is an array
     * which contains all the values in the input arrays. Keys are ignored.
     *
     * If input collections are a mix of arrays and traversables then the output
     * is a lazy traversable which contains all the values in the input collections.
     *
     * @param mixed[] $collections  input collections
     *
     * @return mixed    output collection
     *
     * @throws InvalidArgumentException if any collection is not an array, or
     * traversable.
     */
    public static function concat(...$collections)
    {
        $counts = array_reduce($collections, fn($acc, $c) => [
            "array" => is_array($c) ? ++$acc["array"] : $acc["array"],
            "traversable" => $c instanceof \Traversable ? ++$acc["traversable"] : $acc["traversable"],
            "all" => ++$acc["all"]
        ], ["array" => 0, "traversable" => 0, "all" => 0]);

        if($counts["array"] + $counts["traversable"] < $counts["all"])
        {
            throw new InvalidArgumentException("All collectiosn must be array or traversable");
        }

        if($counts["all"] === 0)
        {
            $out = array();
        }
        else if($counts["array"] === $counts["all"])
        {
            $out = array();
            foreach($collections as $coll)
            {
                foreach($coll as $v)
                {
                    $out[] = $v;
                }
            }
        }
        else
        {
            $fn = function() use($collections) {
                foreach($collections as $coll)
                {
                    // don't use yield from as it preserves keys
                    foreach($coll as $val)
                    {
                        yield $val;
                    }
                }
            };
            $out = self::generatorToIterable($fn);
        }

        return $out;
    }

    /**
     * Joins all the input collections together to form a new output collection.
     *
     * In order to preserve all keys, the returned collection is a Traversable.
     *
     * @param mixed[] $collections  input collections
     *
     * @return Traversable  output collection
     *
     * @throws InvalidArgumentException if any collection is not an array, or
     * traversable.
     */
    public static function concatK(...$collections) : \Traversable
    {
        $counts = array_reduce($collections, fn($acc, $c) => [
            "array" => is_array($c) ? ++$acc["array"] : $acc["array"],
            "traversable" => $c instanceof \Traversable ? ++$acc["traversable"] : $acc["traversable"],
            "all" => ++$acc["all"]
        ], ["array" => 0, "traversable" => 0, "all" => 0]);

        if($counts["array"] + $counts["traversable"] < $counts["all"])
        {
            throw new InvalidArgumentException("All collectiosn must be array or traversable");
        }

        $fn = function() use($collections) {
            yield from [];
            foreach($collections as $coll)
            {
                yield from $coll;
            }
        };
        $out = self::generatorToIterable($fn);
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

    /**
     * Creates a new object by applying a series of transformations to a given
     * object's properties.
     *
     * Transformations are specified using an associative array indexed by
     * object property name. The values of the associative array are
     * transformation functions which are passed the original value of the
     * property.
     *
     * Also works for associative arrays.
     *
     * @param array $spec               The transformations to perform.
     * @param array|object $target      The base object to transform - threadable.
     * @return array|object|callable    Same type as $target, or a callable if
     *                                  $target was null.
     */
    public static function evolve(array $spec, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::evolve($spec, $target);
        }
        if(!is_array($target) && !is_object($target))
        {
            throw new InvalidArgumentException("'target' must be associative array or object");
        }

        $out = $target;
        foreach($spec as $field => $fn)
        {
            if(self::hasProp($field, $out))
            {
                $curr = self::prop($field, $target);
                $out = self::assoc($out, ($fn)($curr), $field);
            }
        }
        return $out;
    }

    /**
     * filter transducer
     *
     * @param callable $predicate       test applied to each value passed in
     *
     * @return callable transducer
     */
    public static function filterT(callable $predicate) : callable
    {
        return fn(callable $step) => fn($acc, $v, $k) => ($predicate($v, $k) ? $step($acc, $v, $k) : $acc);
    }

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function retains keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             optional, collection to filter, threadable
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function. If $target was null then callable.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator.
     */
    public static function filterK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::filterK($predicate, $target);
        }
        if(is_array($target))
        {
            $out = array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH );
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            // transduce but passing assoc as step function, so that key is preserved
            $out = self::transduce(
                self::filterT($predicate),
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

    /**
     * Keeps only those values in the target that match the given predicate
     * function. This filter function ignores keys in the target collection.
     *
     * @param callable $predicate       test applied to each value in $target
     * @param mixed $target             optional, collection to filter, threadable
     *
     * @return mixed a new collection containing only those values in target
     * that satisfy the given predicate function.
     *
     * @throws InvalidArgumentException if target is not an array, object,
     * traversable, or generator. If $target was null then callable.
     */
    public static function filter(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::filter($predicate, $target);
        }
        if(is_array($target))
        {
            $out = array_values(array_filter($target, $predicate, ARRAY_FILTER_USE_BOTH));
        }
        else if(is_object($target) || ($target instanceof \Traversable) || self::isGenerator($target))
        {
            $notTravOrGen = !($target instanceof \Traversable || self::isGenerator($target));
            // use the transduce filter, but ignore key
            $out = self::transduce(
                self::filterT($predicate),
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

    /**
     * Find the first entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return variant the first value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findFirst(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findFirst($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findFirst"))
        {
            return $target->findFirst($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : $acc, null, $target);
        }
    }

    /**
     * Find the index of the first entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return int|string index of the first value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findFirstK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findFirstK($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findFirstK"))
        {
            return $target->findFirstK($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : $acc, -1, $target);
        }
    }

    /**
     * Find the last entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return variant the last value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findLast(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findLast($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findLast"))
        {
            return $target->findLast($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $v : $acc, null, $target);
        }
    }

    /**
     * Find the index of the last entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate       used to determine match (passed $v, $k)
     * @param object|iterable $target   thing to search for match in
     * 
     * @return int|string index of the last value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findLastK(callable $predicate, $target = null)
    {
        if($target === null)
        {
            return fn($target) => self::findLastK($predicate, $target);
        }
        if(is_object($target) && method_exists($target, "findLastK"))
        {
            return $target->findLastK($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $k : $acc, null, $target);
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
            $fnFlatMap = self::pipe(
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

    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: array containing all the values in the input which have the same group
     *
     * @param callable $fnGroup     given input value, derive group
     * @param iterable $coll        optional source collection - threadable
     * 
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupBy(callable $fnGroup, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupBy($fnGroup, $coll);
        }
        
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v) => self::append($acc, $v),
            [],
            $coll
        );
    }

    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: array of all the values in the input with the same group, after being
     * passed through the map function
     *
     * @param callable $fnGroup     given input value, derive group
     * @param callable $fnMap       given input value, map to output value
     * @param iterable $coll        optional source collection - threadable
     * 
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupMapBy(callable $fnGroup, callable $fnMap, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupMapBy($fnGroup, $fnMap, $coll);
        }
        
        return self::groupReduceBy(
            $fnGroup,
            fn($acc, $v, $k) => self::append($acc, $fnMap($v, $k)),
            [],
            $coll
        );
    }

    /**
     * Produces an associative array
     * - keys: generated by passing each value in the input through a mapping function
     * - values: generated by passing each value in the input that is in the same group
     * (as per the mapping function) through a reducing function.
     *
     * @param callable $fnGroup     given input value, derive group
     * @param callable $fnReduce    given accumulator and input value, derive new accumulated value
     * @param mixed $initial        starting value for each reduction
     * @param iterable $coll        optional source collection - threadable
     *
     * @return array|callable grouped collection, or callable if $coll is omitted.
     */
    public static function groupReduceBy(callable $fnGroup, callable $fnReduce, $initial, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::groupReduceBy($fnGroup, $fnReduce, $initial, $coll);
        }
        
        $out = array();
        foreach($coll as $k => $v)
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

    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "append" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param mixed $target             values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function inTo($initial, callable $transducer, $target)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $target);
    }

    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "assoc" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param mixed $target             values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function intoK($initial, callable $transducer, $target)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::assoc($acc, $v, $k), $initial, $target);
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

    /**
     * map transducer
     * 
     * @param callable $func    transform function
     * 
     * @return callable transducer
     */
    public static function mapT(callable $func) : callable
    {
        return fn($step) => fn($acc, $v, $k) => $step($acc, $func($v, $k), $k);
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
                self::mapT($func),
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

    public static function matchT(iterable $criteria) : callable
    {
        return self::filterT(function($v) use($criteria) {
            return self::all(function($func, $field) use($v) {
                return $func(self::prop($field, $v));
            }, $criteria);
        });
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
                self::matchT($criteria),
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

    /**
     * Merge two associative arrays or objects together.
     *
     * Does not support generators / traversables as the result would just be a
     * concatenation.
     */
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

    /**
     * Returns the nth element of a collection.
     *
     * if $n is positive then the $nth value is returned.
     * if $n is negative then the length + $nth value is returned.
     * if $n >= length or length + $n < 0 then null is returned
     *
     * @param int $n                index of element in collection to return
     * @param iterable $target      collection to look in
     *
     * @return mixed    the element at the nth position
     */
    public static function nth(int $n, iterable $target)
    {
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
                $acc++;
            }
        }
        else
        {
            $vals = $isArr ? $target : iterator_to_array($target, false);
            $len = count($vals);
            if($len === 0 || $n >= $len || $len + $n < 0)
            {
                $out = null;
            }
            else
            {
                $idx = ($n >= 0 ? $n : $len + $n);
                $key = array_keys($vals)[$idx];
                $out = $vals[$key];
            }
        }
        return $out;
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

    /**
     * Returns a stateful transducer, which can be called as normal (3 args), or
     * called with 1 argument to cause it to flush through its value.
     *
     * https://github.com/matthiasn/talk-transcripts/blob/master/Hickey_Rich/Transducers/00.34.26.jpg
     * https://github.com/matthiasn/talk-transcripts/blob/master/Hickey_Rich/Transducers/00.36.36.jpg
     * https://www.youtube.com/watch?v=6mTbuzafcII
     * 
     * @param callable $fnGroup
     * @param callable $fnReduce
     * @param type $initial
     * @param callable $step
     * @return type
     */
    public static function partitionReduceByT(callable $fnGroup, callable $fnReduce, $initial, callable $step)
    {
        $started = false;
        $grp = null;
        $cache = null;

        // multi-arity transducer...
        return self::multiArityfunction(
            // arity-1 flushes out any value in the cache (called at the end to
            // get the data left in cache after the last item has been processed)
            function($acc) use(&$started, &$cache, &$grp, $step) {
                return $started ? $step($acc, $cache, $grp) : $acc;
            },
            // arity-3 is normal transducer behaviour
            function($acc, $v, $k) use($fnGroup, $step, &$grp, &$cache, &$started, $fnReduce, $initial) {
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
            }
        );
    }

    /**
     * Splits the input collection into groups (arrays) of contiguous values.
     *
     * Each value in the collection is passed through $fnGroup and whenever the
     * result changes a new group is started.
     *
     * Each group is indexed by the result of calling $fnGroup.
     *
     * @param callable $fnGroup         determines when to partition input collection,
     *                                  and index to use for output values.
     * @param iterable $collection      input collection
     *
     * @return iterable (same type as $collection)
     */
    public static function partitionBy(callable $fnGroup, iterable $collection)
    {
        return self::transduce(
            fn($step) => self::partitionByT($fnGroup, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
        );
    }

    /**
     * Splits the input collection into groups (arrays) of contiguous values.
     * Each value in the array is passed through a map function.
     *
     * Each value in the collection is passed through $fnGroup and whenever the
     * result changes a new group is started.
     *
     * Each group is indexed by the result of calling $fnGroup.
     *
     * @param callable $fnGroup         determines when to partition input collection,
     *                                  and index to use for output values.
     * @param callable $fnMap           each value in the input collection is mapped
     *                                  to create the corresponding value in the output
     *                                  collection
     * @param iterable $collection      input collection
     *
     * @return iterable (same type as $collection)
     */
    public static function partitionMapBy(callable $fnGroup, callable $fnMap, iterable $collection)
    {
        return self::transduce(
            fn($step) => self::partitionMapByT($fnGroup, $fnMap, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
        );
    }

    /**
     * Splits the input collection into groups of contiguous values.
     *
     * Each value in the collection is passed through $fnGroup and whenever the
     * result changes a new group is started.
     *
     * Each group is then reduced using a reducer to form the value in the
     * output collection.
     * 
     * Each value in the output collection is indexed by the value returned by
     * $fnGroup.
     * 
     * @param callable $fnGroup         determines when to partition input collection,
     *                                  and index to use for output values.
     * @param callable $fnReducer       each group of values in the input collection
     *                                  are reduced using this reducer to form the
     *                                  values in the output collection.
     * @param mixed $initial            cloned to produce the initial value for each
     *                                  group reduction
     * @param iterable $collection      input collection
     *
     * @return iterable (same type as $collection)
     */
    public static function partitionReduceBy(callable $fnGroup, callable $fnReducer, $initial, iterable $collection)
    {
        return self::transduce(
            fn($step) => self::partitionReduceByT($fnGroup, $fnReducer, $initial, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
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

    /**
     * Transducer for the skip functions.
     *
     * Creates a new step function which when called skips over the first $count
     * values, only passing every value after that to the passed in step function.
     *
     * @param int $count        Number of items to skip
     * @param callable $step    Step function
     * 
     * @return callable
     */
    public static function skipT(int $count, callable $step) : callable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        $skipped = 0;
        return function($acc, $v, $k) use($count, $step, &$skipped)
        {
            if($skipped < $count)
            {
                $skipped++;
                return $acc;
            }
            else
            {
                return $step($acc, $v, $k);
            }
        };
    }

    /**
     * Transducer for skip-while functions
     *
     * Given a predicate and a step function, creates a new step function
     * that when called skips any leading values up until the first leading
     * value that matches the given predicate.
     *
     * @param callable $pred        predicate function
     * @param callable $step        step function
     *
     * @return callable
     */
    public static function skipWhileT(callable $pred, callable $step) : callable
    {
        $skipping = true;
        return function($acc, $v, $k) use($pred, $step, &$skipping)
        {
            $skipping = $skipping && $pred($v, $k);
            if(!$skipping)
            {
                return $step($acc, $v, $k);
            }
            else
            {
                return $acc;
            }
        };
    }

    /**
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count            Number of items to skip.
     * @param iterable $collection  Collection whose starting items will be skipped.
     *
     * @return iterable new collection with leading $count items removed.
     */
    public static function skip(int $count, iterable $collection) : iterable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if(is_array($collection))
        {
            $out = array_values(array_slice($collection, $count));
        }
        else
        {
            $out = self::transduce(
                fn($step) => self::skipT($count, $step),
                fn($acc, $v) => self::append($acc, $v),
                self::emptied($collection),
                $collection
            );
        }
        return $out;
    }

    /**
     * Creates and returns a collection of the same type as the input but with the
     * first $count items removed.
     *
     * @param int $count            Number of items to skip.
     * @param iterable $collection  Collection whose starting items will be skipped.
     *
     * @return iterable new collection with leading $count items removed. Retains
     * keys from input collection.
     */
    public static function skipK(int $count, iterable $collection) : iterable
    {
        if($count < 0)
        {
            throw new InvalidArgumentException("'count' cannot be negative");
        }
        if(is_array($collection))
        {
            $out = array_slice($collection, $count);
        }
        else
        {
            $out = self::transduce(
                fn($step) => self::skipT($count, $step),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($collection),
                $collection
            );
        }
        return $out;
    }

    /**
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $collection      Input collection
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed.
     */
    public static function skipWhile(callable $pred, iterable $collection) : iterable
    {
        $out = self::transduce(
            fn($step) => self::skipWhileT($pred, $step),
            fn($acc, $v) => self::append($acc, $v),
            self::emptied($collection),
            $collection
        );
        return $out;
    }

    /**
     * Returns a new collection that omits all leading items in the input collection
     * up until the first item to satisfy the given predicate.
     *
     * @param callable $pred            Test items in the input collection
     * @param iterable $collection      Input collection
     *
     * @return iterable new collection with leading items that fail the predicate
     * removed. Retains keys from input collection.
     */
    public static function skipWhileK(callable $pred, iterable $collection) : iterable
    {
        $out = self::transduce(
            fn($step) => self::skipWhileT($pred, $step),
            fn($acc, $v, $k) => self::assoc($acc, $v, $k),
            self::emptied($collection),
            $collection
        );
        return $out;
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
            // do our own reduction here as we need to know whether we exited
            // early or not, so that we know whether or not to try to flush
            // the transducer
            $out = $initial;
            $reducer = $transducer($step);
            foreach($collection as $k => $v)
            {
                $out = $reducer($out, $v, $k);
                if($out instanceof Reduced)
                {
                    return $out->v;
                }
            }

            try
            {
                $out = $reducer($out);
            }
            catch (ArgumentCountError $ex)
            {
            }
            return $out;
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

    /**
     * Checks if all given values are within the given list.
     *
     * @param iterable $vals        the values to search for
     * @param iterable $list        the list to search within
     *
     * @return bool True if all values in list, false otherwise
     */
    public static function includesAll(iterable $vals, iterable $list)
    {
        return self::all(fn($v) => self::includes($v, $list), $vals);
    }

    /**
     * Checks if any given values are within the given list.
     *
     * @param iterable $vals        the values to search for
     * @param iterable $list        the list to search within
     *
     * @return bool True if any values in list, false otherwise
     */
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

    public static function multiArityFunction(callable ...$fns)
    {
        $arityMap = array();
        foreach($fns as $fn)
        {
            $refFn = new \ReflectionFunction($fn);
            $arity = $refFn->getNumberOfParameters();
            $arityMap[$arity] = $fn;
        }

        return function(...$args) use($arityMap)
        {
            $argCount = count($args);
            if(array_key_exists($argCount, $arityMap))
            {
                return ($arityMap[$argCount])(...$args);
            }
            else
            {
                throw new Exception("Invalid number of arguments for multi arity function");
            }
        };
    }

    public static function memoize(callable $fn)
    {
        return function(...$args) use($fn) {
            static $prev = array();
            $v = self::findFirst(fn($v) => self::propEq(0, $args, $v), $prev);
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