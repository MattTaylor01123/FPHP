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
     * Creates a new sequence from an existing sequence by applying a
     * transducer to the existing sequence.
     * 
     * @param callable $transducer  transducer function
     * @param callable $step        step function
     * @param mixed $initial        output initial value
     * @param mixed $sequence   input to transduce
     * 
     * @return mixed transduced sequence
     */
    public static function transduce(callable $transducer, callable $step, $initial, $sequence)
    {
        if($initial instanceof Traversable)
        {
            return new TransformedTraversable($transducer, $step, $sequence);
        }
        else
        {
            // do our own reduction here as we need to know whether we exited
            // early or not, so that we know whether or not to try to flush
            // the transducer
            $out = $initial;
            $reducer = $transducer($step);
            foreach($sequence as $k => $v)
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
     * Creates a new collection from two existing collections by applying a 2D
     * transducer to the existing collections.
     * 
     * @param callable $transducer2D    transducer function
     * @param callable $step            step function
     * @param mixed $initial            output initial value
     * @param iterable $sequence1       input to transduce
     * @param iterable $sequence2       input to transduce
     * 
     * @return mixed result
     */
    public static function transduce2(callable $transducer2D, callable $step, $initial, iterable $sequence1, iterable $sequence2)
    {
        if($sequence1 instanceof Traversable)
        {
            return new TransformedTraversable2($transducer2D, $step, $sequence1, $sequence2);
        }

        // do our own reduction here as we need to know whether we exited
        // early or not, so that we know whether or not to try to flush
        // the transducer
        $out = $initial;
        $reducer = $transducer2D($step);
        foreach($sequence1 as $k1 => $v1)
        {
            foreach($sequence2 as $k2 => $v2)
            {
                $out = $reducer($out, $v1, $v2, $k1, $k2);
                if($out instanceof Reduced)
                {
                    return $out->v;
                }
            }
            
            try
            {
                $out = $reducer($out, $v1, $k1);
            }
            catch (ArgumentCountError $ex)
            {
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