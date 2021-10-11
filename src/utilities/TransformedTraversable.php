<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\utilities;

use FPHP\collection\Reduced;
use FPHP\FPHP;
use IteratorAggregate;
use JsonSerializable;
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
        $set = false;
        $step = $this->step;

        $stepWrapper = function(...$args) use($step, &$set) {
            $acc = new IterableGenerator(fn() => yield from []);
            $set = true;
            return $step($acc, ...array_slice($args, 1));
        };

        $initial = new IterableGenerator(fn() => yield from []);

        $transducer = $this->transducer;
        $reducer = $transducer($stepWrapper);
        foreach($this->traversable as $k => $v)
        {
            $set = false;
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
    }

    public function jsonSerialize()
    {
        return FPHP::iterableToArray($this->getIterator());
    }
}