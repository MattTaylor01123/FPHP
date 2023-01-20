<?php

/*
 * (c) Matthew Taylor
 */

namespace src\utilities;

use ArgumentCountError;
use FPHP\FPHP;
use IteratorAggregate;
use JsonSerializable;
use src\collection\Reduced;
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
        $step = $this->step;

        $stepWrapper = function(...$args) use($step) {
            $acc = new IterableGenerator(function() {
                yield from [];
            });
            return $step($acc, ...array_slice($args, 1));
        };

        $initial = new IterableGenerator(function() {
            yield from [];
        });
        $transducer = $this->transducer;
        $reducer = $transducer($stepWrapper);
        $curr = null;
        foreach($this->traversable as $k => $v)
        {
            $curr = $reducer($initial, $v, $k);
            if($curr instanceof Reduced)
            {
                yield from $curr->v;
                break;
            }
            else
            {
                yield from $curr;
            }
        }
        if(!($curr instanceof Reduced))
        {
            try
            {
                yield from $reducer($initial);
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