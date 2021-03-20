<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP\utilities;

use FPHP\FPHP;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

final class IterableGenerator implements IteratorAggregate, JsonSerializable
{
    private $generator;

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function getIterator(): Traversable
    {
        $fn = $this->generator;
        return $fn();
    }

    public function jsonSerialize()
    {
        $out = FPHP::pipex(
            iterator_to_array($this->getIterator(), true),
            fn($a) => FPHP::isSequentialArray($a) ? FPHP::values($a) : $a
        );
        return $out;
    }
}