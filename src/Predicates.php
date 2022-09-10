<?php

/*
 * (c) Matthew Taylor
 */

namespace src;

use Closure;
use Generator;
use ReflectionFunction;
use stdClass;
use Traversable;

trait Predicates
{
    public static function isArray(...$args)
    {
        $isArray = self::curry(function($v) {
            return is_array($v);
        });
        return $isArray(...$args);
    }

    public static function isBool(...$args)
    {
        $isBool = self::curry(function($v) {
            return is_bool($v);
        });
        return $isBool(...$args);
    }

    public static function isEmpty(...$args)
    {
        $isEmpty = self::curry(function($v) {
            if(self::isString($v))
            {
                return strlen($v) === 0;
            }
            if(is_iterable($v))
            {
                return self::length($v) === 0;
            }
            if(is_object($v))
            {
                return self::length(self::keys($v)) === 0;
            }
            if($v === null)
            {
                return false;
            }
            return false;
        });
        return $isEmpty(...$args);
    }
    
    public static function isFloat(...$args)
    {
        $isFloat = self::curry(function($v) {
            return is_float($v);
        });
        return $isFloat(...$args);
    }

    public static function isGenerator(...$args)
    {
        $isGenerator = self::curry(function($v) {
            if($v instanceof Generator)
            {
                return true;
            }
            if($v instanceof Closure &&
               (new ReflectionFunction($v))->isGenerator())
            {
                return true;
            }
        });
        return $isGenerator(...$args);
    }

    public static function isInteger(...$args)
    {
        $isInteger = self::curry(function($v) {
            return is_int($v);
        });
        return $isInteger(...$args);
    }

    public static function isIterable(...$args)
    {
        $isGenerator = self::curry(function($arg) {
            return is_iterable($arg);
        });
        return $isGenerator(...$args);
    }

    public static function isObject(...$args)
    {
        $isObject = self::curry(function($v) {
            return is_object($v);
        });
        return $isObject(...$args);
    }

    public static function isSequentialArray($target)
    {
        // https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
        $out = false;
        if(is_array($target) && count($target) > 0 && array_key_exists(0, $target))
        {
            $out = (array_keys($target) === range(0, count($target) - 1));
        }
        return $out;
    }

    public static function isString(...$args)
    {
        $isString = self::curry(function($v) {
            return is_string($v);
        });
        return $isString(...$args);
    }

    public static function isType(...$args)
    {
        $isType = self::curry(function($t, $v) {
            return gettype($v) === $t;
        });
        return $isType(...$args);
    }

    public static function isClass(...$args)
    {
        $isClass = self::curry(function($c, $v) {
            return get_class($v) === $c;
        });
        return $isClass(...$args);
    }

    public static function isStdClass(...$args)
    {
        return self::isA(stdClass::class, ...$args);
    }
    
    public static function isTraversable(...$args)
    {
        return self::isA(Traversable::class, ...$args);
    }

    public static function isA(...$args)
    {
        $isA = self::curry(function($class, $v) {
            return is_a($v, $class);
        });
        return $isA(...$args);
    }

    public static function isScalarType(...$args)
    {
        $isScalar = self::curry(function($v) {
            return self::includes(gettype($v), ["boolean", "double", "integer", "string"]);
        });
        return $isScalar(...$args);
    }

    public static function isNull(...$args)
    {
        $isNull = self::curry(function($v) {
            return is_null($v);
        });
        return $isNull(...$args);
    }

    public static function test(...$args)
    {
        $test = self::curry(function(string $regex, string $str) {
            return preg_match($regex, $str) === 1;
        });
        return $test(...$args);
    }
}