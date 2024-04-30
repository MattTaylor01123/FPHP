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

final class TransformedTraversable implements IteratorAggregate, JsonSerializable
{
    private $transducer = null;
    private $traversable = null;
    private $step = null;
    public function __construct($transducer, $step, $traversable)
    {
        $this->transducer = $transducer;
        $this->step = $step;
        $this->traversable = $traversable;
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
            
            // as prepend cannot be supported without making the traversable
            // eager
            
            // do not support assoc as that can update existing keys, which
            // again forces us to be eager.
        };

        $reducer = ($this->transducer)($this->step);
        foreach($this->traversable as $k => $v)
        {
            $current = $reducer($current, $v, $k);
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