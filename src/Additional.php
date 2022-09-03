<?php

/*
 * (c) Matthew Taylor
 */

namespace FPHP;

use ReflectionClass;
use stdClass;

trait Additional
{
    public static function mapTo(...$args)
    {
        $mapTo = self::curry(function(string $className, iterable $iterable) {
            if(is_object($iterable) && method_exists($iterable, "mapTo"))
            {
                return $iterable->mapTo($className);
            }
            else
            {
                $type = new ReflectionClass($className);
                $params = self::pipex(
                    $type->getConstructor()->getParameters(),
                    self::indexBy(self::invoker(0, "getName"))
                );
                return self::map(function($v) use($params, $type) {
                    if(is_array($v))
                    {
                        $in = $v;
                    }
                    elseif(is_iterable($v))
                    {
                        $in = iterator_to_array($v);
                    }
                    elseif($v instanceof stdClass)
                    {
                        $in = (array)$v;
                    }

                    $args = self::map(function($v, $k) use($in) {
                        return $in[$k];
                    }, $params);
                    return $type->newInstanceArgs($args);
                }, $iterable);
            }
        });
        return $mapTo(...$args);
    }
}