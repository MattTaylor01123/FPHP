<?php

/*
 * (c) Matthew Taylor
 */

namespace src\collection;

trait Partition
{
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
}