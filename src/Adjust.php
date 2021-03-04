<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

trait Adjust 
{
    public function adjust(...$params)
    {
        $adjust = self::curry(function($idx, callable $transform, $list) {
            $transducer = fn($step) => 
                fn($acc, $v, $k) => $step($acc, $k === $idx ? $transform($v, $k) : $v, $k);
            
            if(is_array($list))
            {
                return self::transduce($transducer, self::append(), []);
            }
            else
            {
                return self::transduce($transducer, fn($acc, $v, $k) => yield $k => $v, null);
            }
        });
        return $adjust(...$params);
    }
}
