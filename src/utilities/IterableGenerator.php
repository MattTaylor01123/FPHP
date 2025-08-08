<?php

/*
 * (c) Matthew Taylor
 */

namespace src\utilities;

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

    public function jsonSerialize() : mixed
    {
        return FPHP::iterableToArray($this->getIterator());
    }
}