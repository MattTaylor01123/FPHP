<?php

/*
 * (c) Matthew Taylor
 */

namespace src\utilities;

use ArgumentCountError;
use FPHP\FPHP;
use IteratorAggregate;
use JsonSerializable;
use src\sequence\Reduced;
use Traversable;

final class TransformedTraversable2 implements IteratorAggregate, JsonSerializable
{
    private $transducer = null;
    private $traversable = null;
    private $traversable2 = null;
    private $step = null;
    public function __construct($transducer, $step, $traversable, $traversable2)
    {
        $this->transducer = $transducer;
        $this->step = $step;
        $this->traversable = $traversable;
        $this->traversable2 = $traversable2;
    }
    public function getIterator(): Traversable
    {        
        $current = new class() 
        {
            public $vals = [];
            private $i = 0;
            public $set = false;
            
            public function append($v)
            {
                $this->vals[] = [$this->i, $v];
                $this->i++;
                $this->set = true;
                return $this;
            }
            
            public function appendK($v, $k)
            {
                $this->vals[] = [$k, $v];
                $this->set = true;
                return $this;
            }

            /**
             * prepend cannot be lazy - have to process the entirety of the input
             * sequence to find out which value will be at the start of the output
             * sequence.
             * 
             * Therefore, every time values are to be pre-pended, keep track of them
             * but don't flag as data available (set = true).
             * 
             * Then, when no values are passed in, treat as 1-arity reducer and flush
             * the accumulated values.
             */
            public function prepend($v = "__DEF__")
            {
                if($v === "__DEF__")
                {
                    $this->set = true;
                    foreach($this->vals as list(&$k,))
                    {
                        $k = ($this->i - 1) - $k;
                    }
                    return $this;
                }
                $this->vals = [[$this->i, $v], ...$this->vals];
                $this->i++;
                return $this;
            }
            
            /**
             * prependK cannot be lazy - have to process the entirety of the input
             * sequence to find out which value will be at the start of the output
             * sequence.
             * 
             * Therefore, every time values are to be prepended, keep track of them
             * but don't flag as data available (set = true).
             * 
             * Then, when no values are passed in, treat as 1-arity reducer and flush
             * the accumulated values.
             */
            public function prependK($v = "__DEF__", $k = "__DEF__")
            {
                if($v === "__DEF__" && $k === "__DEF__")
                {
                    $this->set = true;
                    return $this;
                }
                $this->vals = [[$k, $v], ...$this->vals];
                return $this;
            }
        };

        $reducer = ($this->transducer)($this->step);
        foreach($this->traversable as $k => $v)
        {
            foreach($this->traversable2 as $k2 => $v2)
            {
                $current = $reducer($current, $v, $v2, $k, $k2);
                if($current instanceof Reduced)
                {
                    if($current->v->set)
                    {
                        foreach($current->v->vals as list($k, $v))
                        {
                            yield $k => $v;
                        }
                        $current->v->set = false;
                        $current->v->vals = [];
                    }
                    break;
                }
                else if($current->set)
                {
                    foreach($current->vals as list($k, $v))
                    {
                        yield $k => $v;
                    }
                    $current->set = false;
                    $current->vals = [];
                }
            }
            
            // flush inner traversable
            if(!($current instanceof Reduced))
            {
                try
                {
                    $current = $reducer($current, $v, $k);
                    if($current->set)
                    {
                        foreach($current->vals as list($k, $v))
                        {
                            yield $k => $v;
                        }
                        $current->set = false;
                        $current->vals = [];
                    }
                }
                catch (ArgumentCountError $ex)
                {
                }
            }
        }
        
        // flush outer traversable
        if(!($current instanceof Reduced))
        {
            try
            {
                $current = $reducer($current);
                if($current->set)
                {
                    foreach($current->vals as list($k, $v))
                    {
                        yield $k => $v;
                    }
                    $current->set = false;
                    $current->vals = [];
                }
            }
            catch (ArgumentCountError $ex)
            {
            }
        }
    }

    public function jsonSerialize()
    {
        return FPHP::iterableToArray($this->getIterator());
    }
}