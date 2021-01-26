<?php

/*
 * (c) Matthew Taylor
 */

namespace RamdaPHP;

trait Adjust 
{
    public function adjust(...$params)
    {
        $adjust = R::curry(function($idx, callable $transform, iterable $list) {
            $transducer = fn($step) => 
                fn($acc, $v, $k) => $k === $step($acc, $idx ? $transform($v, $k) : $v, $k);
            
            if(is_array($list))
            {
                return R::transduce($transducer, R::append(), []);
            }
            else
            {
                return R::transduce($transducer, fn($acc, $v, $k) => yield $k => $v, null);
            }
        });
        return $adjust(...$params);
    }
}
