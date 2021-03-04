<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use stdClass;

trait Equals 
{
    public static function equals(...$args)
    {
        $equals = self::curry(function($v1, $v2) : bool {
            if($v1 === null || $v2 === null)
            {
                return false;
            }
            if($v1 === $v2)
            {
                return true;
            }
            $t1 = gettype($v1);
            $t2 = gettype($v2);
            if($t1 !== $t2)
            {
                return false;
            }
            if(self::isArray($v1) || $v1 instanceof stdClass)
            {
                $v1Keys = self::isArray($v1) ? array_keys($v1) : array_keys((array)$v1);
                $v2Keys = self::isArray($v2) ? array_keys($v2) : array_keys((array)$v2);
                if(!self::includesAll($v1Keys, $v2Keys) ||
                   !self::includesAll($v2Keys, $v1Keys))
                {
                    return false;
                }
                for($i = 0; $i < count($v1Keys); $i++)
                {
                    $k = $v1Keys[$i];
                    if(!self::equals(self::prop($k, $v1), self::prop($k, $v2)))
                    {
                        return false;
                    }
                }
                return true;
            }
            return false;
        });
        return $equals(...$args);
    }
}
