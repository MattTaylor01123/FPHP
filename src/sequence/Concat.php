<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use InvalidArgumentException;

trait Concat
{
    // string concatenation already has its own custom operator in PHP. No desire
    // to blur two fundamentally different behaviours at present.

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
}