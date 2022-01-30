# FPHP

## Motivations

- PHP has support for iterators and generators but its built-in collection functions work only on arrays (e.g. array_map, array_filter, array_reduce).
- PHP's built-in collection functions have behaviour inconsistent with other language implementations and the parameter order is inconsistent.
- Improve ease of usage of lazy evaluation via iterators and generators through collection functions which support laziness.
- Support for partial application of functions, to support a more declerative style of coding.

## Inspirations

- [Ramda](https://ramdajs.com/) for JavaScript.
- [Clojure](https://clojure.org/)

## Description

### Support for arrays, associative arrays, and objects

```
$fnTransform = F::pipe(
    F::map(fn($v) => $v * 2),
    F::take(3)
);

$arr = [1,2,3,4,5];
$resArr = $fnTransform($arr);
$this->assertEquals([2,4,6], $resArr);

$assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$actAssocArr = $fnTransform($assocArr);
$this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $actAssocArr);

$obj = (object)["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$actObj = $fnTransform($obj);
$this->assertEquals((object)["a" => 2, "b" => 4, "c" => 6], $actAssocArr);
```
