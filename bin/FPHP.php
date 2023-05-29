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
use src\sequence\Reduced;
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

    public static function adjust($idx, callable $transform, ?iterable $collection = null)
    {
        if($collection === null)
        {
            return fn($collection) => self::adjust($idx, $transform, $collection);
        }
        return self::transduce(
            fn($step) => self::adjustT($idx, $transform, $step),
            // preserve keys (array itself isn't mutated, only elements)
            self::defaultStepK($collection),
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
     * @param iterable|object $acc  input collection or object with appendK method
     * @param mixed $val            value to append to end of collection
     * @param mixed $key            key to associate with value
     *
     * @return Traversable|object new collection or return value from $acc->appendK
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function appendK($acc, $val, $key)
    {
        if(is_object($acc) && method_exists($acc, "appendK"))
        {
            return $acc->appendK($val, $key);
        }
        else if(is_array($acc) || self::isTraversable($acc) || self::isGenerator($acc))
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
     * @param iterable|object $acc  input collection or object with "append" method
     * @param mixed $val            value to append to end of new collection
     *
     * @return iterable|object new collection or return value of $acc->append
     *
     * @throws InvalidArgumentException if input collection is not an array or a
     * traversable.
     */
    public static function append($acc, $val)
    {
        if(is_object($acc) && method_exists($acc, "append"))
        {
            return $acc->append($val);
        }
        else if(is_array($acc))
        {
            $out = array_values($acc);
            $out[] = $val;
        }
        else if(self::isTraversable($acc) || self::isGenerator($acc))
        {
            $fn = function() use($val, $acc) {
                // don't yield from as not preserving keys
                $i = 0;
                foreach($acc as $v)
                {
                    yield $i => $v;
                    $i++;
                }
                yield $i => $val;
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

    /**
     * Returns an appropriate (non key preserving) step function for the input
     * 
     * @param object|array $target      input to return appropriate step 
     *                                  function for
     * 
     * @return callable     step function
     * 
     * @throws Exception if a suitable step function could not be found.
     */
    public static function defaultStep($target)
    {
        return fn($acc, $v) => self::append($acc, $v);
    }

    /**
     * Returns an appropriate key preserving step function for the input
     * 
     * @param object|array $target      input to return appropriate step 
     *                                  function for
     * 
     * @return callable     step function
     * 
     * @throws Exception if a suitable step function could not be found.
     */
    public static function defaultStepK($target)
    {
        if(is_object($target) && $target instanceof Traversable)
        {
            $out = fn($acc, $v, $k) => self::appendK($acc, $v, $k);
        }
        else if(is_array($target) || is_object($target))
        {
            $out = fn($acc, $v, $k) => self::assoc($acc, $v, $k);
        }
        else
        {
            throw new Exception("Not possible to determine a step function for type " . gettype($target));
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
            // preserve keys
            $out = self::transduce(
                self::filterT($predicate),
                self::defaultStepK($target),
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
                self::defaultStep($target),
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
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return variant the first value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findFirst(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findFirst($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findFirst"))
        {
            return $sequence->findFirst($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($v) : $acc, null, $sequence);
        }
    }

    /**
     * Find the index of the first entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return int|string index of the first value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findFirstIndex(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findFirstIndex($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findFirstIndex"))
        {
            return $sequence->findFirstIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? new Reduced($k) : $acc, -1, $sequence);
        }
    }

    /**
     * Find the last entry in a sequence that satisfies the predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return variant the last value that satisfies the predicate, or null if 
     * no match can be found
     */
    public static function findLast(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findLast($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findLast"))
        {
            return $sequence->findLast($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $v : $acc, null, $sequence);
        }
    }

    /**
     * Find the index of the last entry in a sequence that satisfies the 
     * predicate
     * 
     * @param callable $predicate           used to determine match (passed $v, $k)
     * @param object|iterable $sequence     thing to search for match in
     * 
     * @return int|string index of the last value that satisfies the predicate, or -1 
     * if no match can be found
     */
    public static function findLastIndex(callable $predicate, $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::findLastIndex($predicate, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "findLastIndex"))
        {
            return $sequence->findLastIndex($predicate);
        }
        else
        {
            return self::reduce(fn($acc, $v, $k) => $predicate($v, $k) ? $k : $acc, -1, $sequence);
        }
    }

    /**
     * Returns the first element in the sequence
     * 
     * @param mixed $target     an iterable or an object that implements
     *                          "first". Threadable.
     * 
     * @return mixed            the first element in the sequence
     * 
     * @throws InvalidArgumentException if $target is not iterable and is not
     * an object that implements "first".
     */
    public static function first($target = null)
    {
        if($target === null)
        {
            return fn($target) => self::first($target);
        }
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

    /**
     * flatten transducer
     * 
     * @return callable transducer
     */
    public static function flattenT() : callable
    {
        return fn($step) => fn($acc, $v) => 
            self::reduce($step, $acc, is_iterable($v) ? $v : [$v]);
    }

    /**
     * flatMap transducer
     * 
     * @param callable $transform       map transformation function
     * 
     * @return callable transducer
     */
    public static function flatMapT(callable $transform) : callable
    {
        return self::pipe(
            self::flattenT(),
            self::mapT($transform));
    }

    /**
     * Applies a map transformation to every element in the sequence, and then
     * flattens the sequence one level.
     * 
     * @param callable $transform       transformation function
     * @param iterable|null $sequence   the sequence to map & flatten. Threadable
     * 
     * @return mixed     the mapped & flattened sequence. If $sequence is null
     * then a callable is returned.
     */
    public static function flatMap(callable $transform, ?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::flatMap($transform, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "flatMap"))
        {
            return $sequence->flatMap($transform);
        }
        else
        {
            return self::transduce(
                self::flatMapT($transform), 
                fn($acc, $v) => self::append($acc, $v), 
                self::emptied($sequence), 
                $sequence
            );
        }
    }

    /**
     * Flattens a sequence by one level
     * 
     * @param iterable|null $sequence       sequence to flatten. Threadable
     * 
     * @return mixed the flattened sequence. If $sequence is null then a
     * callable is returned.
     */
    public static function flatten(?iterable $sequence = null)
    {
        if($sequence === null)
        {
            return fn($sequence) => self::flatten($sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "flatten"))
        {
            return $sequence->flatten();
        }
        else
        {
            return self::transduce(
                self::flattenT(), 
                fn($acc, $v) => self::append($acc, $v), 
                self::emptied($sequence), 
                $sequence
            );
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

    /**
     * indexBy transducer
     * 
     * @param callable $func        mapping function
     * 
     * @return callable transducer function
     */
    public static function indexByT(callable $func) : callable
    {
        return fn(callable $step) => fn($acc, $v, $k) => $step($acc, $v, $func($v, $k));
    }

    /**
     * Creates a new sequence indexed by the result of calling the mapping
     * function on each element.
     * 
     * @param callable $func                maps sequence values onto keys
     * @param iterable|object $sequence     the sequence to index. Threadable
     * 
     * @return iterable|object|callable  same as type of $sequence, or else 
     * callable if $sequence is null.
     * 
     * @throws InvalidArgumentException if $sequence is not an iterable or an
     * object with a method called "indexBy".
     */
    public static function indexBy(callable $func, $sequence = null)
    {
        if(is_null($sequence))
        {
            return fn($sequence) => self::indexBy($func, $sequence);
        }
        if(is_object($sequence) && method_exists($sequence, "indexBy"))
        {
            $out = $sequence->indexBy($func);
        }
        else if(is_array($sequence) || $sequence instanceof Traversable)
        {
            $out = self::transduce(
                self::indexByT($func),
                fn($acc, $v, $k) => self::assoc($acc, $v, $k),
                self::emptied($sequence),
                $sequence
            );
        }
        else
        {
            throw new InvalidArgumentException("Invalid argument type for 'sequence'");
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
     * @param iterable $sequence        values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function inTo($initial, callable $transducer, iterable $sequence)
    {
        return self::transduce($transducer, fn($acc, $v) => self::append($acc, $v), $initial, $sequence);
    }

    /**
     * Transforms every element in target before accumulating using initial as the
     * start value for the accumulation, and the "assoc" function.
     *
     * @param mixed $initial            start value for accumulation
     * @param callable $transducer      transducer
     * @param iterable $sequence        values to transform
     *
     * @return mixed contains the values in target transformed by the transducer. Type is
     * the same as or compatible with the type of initial.
     */
    public static function intoK($initial, callable $transducer, iterable $sequence)
    {
        return self::transduce($transducer, fn($acc, $v, $k) => self::appendK($acc, $v, $k), $initial, $sequence);
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
        else if( is_object($coll) || is_array($coll) || self::isTraversable($coll) || self::isGenerator($coll))
        {
            // map preserves keys, so use K step
            $out = self::transduce(
                self::mapT($func),
                self::defaultStepK($coll),
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
            self::defaultStepK($collection),
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
            self::defaultStepK($collection),
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
            self::defaultStepK($collection),
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

    /**
     * Like SQL's 'select' but for iterables of maps instead of tables of rows.
     * Maps an iterable of maps picking only the given properties from the maps.
     * If a property does not exist in a map then it is ignored (and is not
     * present in the final map).
     * 
     * @param array $properties     only these properties will be included in
     *                              the output maps
     * @param iterable $coll        an iterable of maps, threadable
     * 
     * @return iterable|callable    an iterable of maps. If $coll was null
     * then callable.
     */
    public static function project(array $properties, ?iterable $coll = null)
    {
        if(is_null($coll))
        {
            return fn($coll) => self::project($properties, $coll);
        }
        if(is_object($coll) && method_exists($coll, "project"))
        {
            $out = $coll->project($properties);
        }
        else
        {
            $out = self::map(fn($v) => self::pick($properties, $v), $coll);
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

    public function __construct($v)
    {
        $this->v = $v;
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
                self::defaultStep($collection),
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
                self::defaultStepK($collection),
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
            self::defaultStep($collection),
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
            self::defaultStepK($collection),
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
            // step preserves keys, so use K step
            return self::transduce(
                fn($step) => self::takeT($count, $step),
                self::defaultStepK($target),
                self::emptied($target),
                $target
            );
        }
    }

    /**
     * takeWhile transducer
     * 
     * @param callable $pred    predicate
     * 
     * @return callable transducer
     */
    public static function takeWhileT(callable $pred) : callable
    {
        return function($step) use($pred) {
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
        };
    }

    /**
     * Create a collection containing all the values from the start of the
     * input collection up to the first value that does not satisfy the given
     * predicate.
     * 
     * @param callable $pred    predicate
     * @param iterable $coll    collection to read values from, threadable
     * 
     * @return iterable|callable new collection with only the taken values, or
     * a callable if $coll was null.
     */
    public static function takeWhile(callable $pred, ?iterable $coll = null)
    {
        if($coll === null)
        {
            return fn(iterable $coll) => self::takeWhile($pred, $coll);
        }
        // takeWhile preserves keys so use K step
        return self::transduce(
            self::takeWhileT($pred),
            self::defaultStepK($coll),
            self::emptied($coll),
            $coll
        );
    }

    /**
     * Creates a new collection from an existing collection by applying a
     * transducer to the existing collection.
     * 
     * @param callable $transducer  transducer function
     * @param callable $step        step function
     * @param mixed $initial        output initial value
     * @param mixed $collection     input to transduce
     * 
     * @return mixed transduced collection
     */
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

    /**
     * values transducer
     * 
     * @param callable $step
     * 
     * @return callable transducer
     */
    public static function valuesT(callable $step) : callable
    {
        return function($acc, $v) use($step) {
            return $step($acc, $v);
        };
    }

    /**
     * Extracts the values from a collection or the properties from an object
     * 
     * @param iterable|object $target       the collection or object
     * 
     * @return mixed    the values or properties
     * 
     * @throws InvalidArgumentException if $target is not an iterable or an object
     */
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

    /**
     * Returns a new map containing all the key->value pairs in the input
     * map plus the key->value pair defined by the other parameters.
     *
     * @param array|object $map     input map
     * @param mixed $val            value to add to map
     * @param mixed $key            key to use when adding value to map
     *
     * @return array|object    new map (type matches $map input)
     *
     * @throws InvalidArgumentException if input map is not of type array or
     * object.
     */
    public static function assoc($map, $val, $key)
    {
        if(is_object($map) && method_exists($map, "assoc"))
        {
            return $map->assoc($val, $key);
        }
        if(is_array($map))
        {
            $out = $map;
            $out[$key] = $val;
        }
        else if(is_object($map))
        {
            $out = clone $map;
            $out->$key = $val;
        }
        else
        {
            throw new InvalidArgumentException("'map' must be of type array or object");
        }
        return $out;
    }

    /**
     * Removes a key->value pair from a map.
     * 
     * @param object|array $map     input map
     * @param mixed $propName       key to remove from map
     * 
     * @return object|array     new map containing everything key from the
     * source map except the key to be removed.
     * 
     * @throws InvalidArgumentException if map is not an array or an object
     */
    public static function dissoc($map, $propName)
    {
        if(is_array($map))
        {
            $out = $map;
            unset($out[$propName]);
        }
        else if(is_object($map))
        {
            $out = clone $map;
            unset($out->$propName);
        }
        else
        {
            throw new InvalidArgumentException("'map' must be of type array or object");
        }
        return $out;
    }

    /**
     * Creates a new map by applying a series of transformations to a given
     * map's properties.
     *
     * Transformations are specified using an associative array indexed by
     * map property name. The values of the associative array are
     * transformation functions which are passed the original value of the
     * property.
     *
     * @param array $spec               The transformations to perform.
     * @param array|object $map         The base map to transform - threadable.
     * @return array|object|callable    Same type as $map, or a callable if
     *                                  $map was null.
     */
    public static function evolve(array $spec, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::evolve($spec, $map);
        }
        if(!is_array($map) && !is_object($map))
        {
            throw new InvalidArgumentException("'map' must be associative array or object");
        }

        $out = $map;
        foreach($spec as $field => $fn)
        {
            if(self::hasProp($field, $out))
            {
                $curr = self::prop($field, $map);
                $out = self::assoc($out, ($fn)($curr), $field);
            }
        }
        return $out;
    }

    /**
     * Checks if a map contains the given property
     * 
     * @param string        $propName   property to check for
     * @param array|object  $map        map to check in, threadable
     * 
     * @return bool|callable true if the map contains a mapping for the 
     * property, false otherwise. If $map is threaded then callable.
     */
    public static function hasProp(string $propName, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::hasProp($propName, $map);
        }
        
        return ((is_object($map) && property_exists($map, $propName)) ||
                (is_array($map) && key_exists($propName, $map)));
    }

    /**
     * Returns true if the given map has all of the given properties, false
     * otherwise.
     * 
     * @param array $propNames      properties to check for
     * @param array|object $map     map to check in for properties, threadable
     * 
     * @return bool|callable    True if all properties are present in map,
     * false otherwise. Callable if $map is null.
     */
    public static function hasProps(array $propNames, $map = null)
    {
        if($map === null)
        {
            return fn($map) => self::hasProps($propNames, $map);
        }
        return self::all(fn($p) => self::hasProp($p, $map), $propNames);
    }

    /**
     * Merge multiple maps (objects or associative arrays) together.
     * 
     * Merging is performed from left to right. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * leftmost map.
     * 
     * If no maps are provided then an empty stdClass is returned.
     * 
     * If the leftmost map is an object which implements the "mergeAllRight"
     * method then the remaining maps are passed as arguments to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object[] ...$maps   the maps to merge.
     *
     * @return mixed The new map resulting from the merge.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeAllRight(...$maps)
    {
        if(!self::all(fn($map) => is_array($map) || is_object($map), $maps))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
 
        $noMaps = count($maps);
        if($noMaps === 0)
        {
            $out = new \stdClass();
        }
        else if(is_object($maps[0]) && method_exists($maps[0], "mergeAllRight"))
        {
            $first = $maps[0];
            $rest = array_slice($maps, 1);
            $out = $first->mergeAllRight(...$rest);
        }
        else
        {
            $initial = self::emptied($maps[0]);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, $maps);
        }
        return $out;
    }

    /**
     * Merge two maps (objects or associative arrays) together.
     * 
     * Merging is performed from left to right. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * leftmost map.
     * 
     * If the leftmost map is an object which implements the "mergeRight"
     * method then the rightmost map is passed to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object      $map1    map to merge
     * @param array|object|null $map2   map to merge, threadable
     *
     * @return mixed The new map resulting from the merge. If
     * $map2 was null then callable.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeRight($map1, $map2 = null)
    {
        if($map2 === null)
        {
            return fn($map2) => self::mergeRight($map1, $map2);
        }
        
        if(!self::all(fn($map) => is_array($map) || is_object($map), [$map1, $map2]))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        if(is_object($map1) && method_exists($map1, "mergeRight"))
        {
            $out = $map1->mergeRight($map2);
        }
        else
        {
            $initial = self::emptied($map1);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, [$map1, $map2]);
        }

        return $out;
    }

    /**
     * Merge multiple maps (objects or associative arrays) together.
     * 
     * Merging is performed from right to left. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * last map.
     * 
     * If no maps are provided then an empty stdClass is returned.
     * 
     * If the rightmost map is an object which implements the "mergeAllLeft"
     * method then the other maps are passed as arguments to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object[] ...$maps   the maps to merge.
     *
     * @return array|object The new map resulting from the merge.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeAllLeft(...$maps)
    {
        if(!self::all(fn($map) => is_array($map) || is_object($map), $maps))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        $noMaps = count($maps);
        if($noMaps === 0)
        {
            $out = new \stdClass();
        }
        else if(is_object($maps[$noMaps - 1]) && method_exists($maps[$noMaps - 1], "mergeAllLeft"))
        {
            $first = $maps[$noMaps - 1];
            $rest = array_slice($maps, 0, -1);
            $out = $first->mergeAllLeft(...$rest);
        }
        else
        {
            $mapsRev = array_reverse($maps);
            $initial = self::emptied($mapsRev[0]);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, $mapsRev);
        }
        return $out;
    }

    /**
     * Merge two maps (objects or associative arrays) together.
     * 
     * Merging is performed from right to left. A new map is returned (the
     * inputs are not modified). The return type is the same type as the
     * rightmost map.
     * 
     * If the rightmost map is an object which implements the "mergeLeft"
     * method then the leftmost map is passed to this method and
     * the result is returned as the output of this function.
     * 
     * @param array|object      $map1    map to merge
     * @param array|object|null $map2   map to merge, threadable
     *
     * @return mixed The new map resulting from the merge. If
     * $map2 was null then callable.
     * 
     * @throws InvalidArgumentException if any of the inputs are not objects or arrays
     */
    public static function mergeLeft($map1, $map2 = null)
    {
        if($map2 === null)
        {
            return fn($map2) => self::mergeLeft($map1, $map2);
        }
        
        if(!self::all(fn($map) => is_array($map) || is_object($map), [$map1, $map2]))
        {
            throw new InvalidArgumentException("Every map must be an array or an object");
        }
        
        if(is_object($map2) && method_exists($map2, "mergeLeft"))
        {
            $out = $map2->mergeLeft($map1);
        }
        else
        {
            $initial = self::emptied($map2);
            $out = self::reduce(fn($acc, $map) =>
                self::reduce(fn($acc, $v, $k) => self::assoc($acc, $v, $k), $acc, $map), $initial, [$map2, $map1]);
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