<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\collection;

use FPHP\utilities\TransformedTraversable;
use Traversable;

trait Transduce
{
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
}