<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

use Exception;
use IteratorAggregate;
use Traversable;

// based on https://stackoverflow.com/questions/5863128/ordering-of-parameters-to-make-use-of-currying
// Chris Okasaki view, for accumulators, put the most varying argument last, e.g.
// the value.

/**
 * Concat is merge but ignores keys
 */
trait Concat
{
    public static function concat(...$args)
    {
        $concat = self::curry(function($v1, $v2) {
            $v1t = gettype($v1);
            $v2t = gettype($v2);
            $v1type = $v1t === "object" ? get_class($v1) : $v1t;
            $v2type = $v2t === "object" ? get_class($v2) : $v2t;

            if($v1type !== $v2type)
            {
                throw new Exception("v1 and v2 must be of the same type");
            }

            if(method_exists($v1, "concat"))
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
                $out = new class($v1, $v2) implements IteratorAggregate
                {
                    private $first;
                    private $second;
                    public function __construct($first, $second)
                    {
                        $this->first = $first;
                        $this->second = $second;
                    }

                    public function getIterator(): Traversable
                    {
                        foreach($this->first as $v)
                        {
                            yield $v;
                        }
                        foreach($this->second as $v)
                        {
                            yield $v;
                        }
                    }
                };
            }
            else
            {
                throw new Exception("v1 and v2 of unhandled type");
            }
            return $out;
        });
        return $concat(...$args);
    }
}