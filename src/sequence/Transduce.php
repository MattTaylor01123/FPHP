<?php

/*
 * (c) Matthew Taylor
 */

namespace src\sequence;

use ArgumentCountError;
use FPHP\utilities\TransformedTraversable;
use Traversable;

trait Transduce
{
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
}