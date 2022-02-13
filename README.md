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

### Partial application

This library supports partial application. Library functions can be called providing 0 or more of the function's arguments. If all arguments are provided, then the function is invoked, otherwise a new function is returned which has arity of the original function's arity - the number of arguments provided. E.g. the assoc function takes 3 arguments: the target collection, the value to associate, and the key to associate with the value. The following are all valid calls to assoc:

```
use FPHP\FPHP as F;

$out1 = F::assoc();
$this->assertTrue(is_callable($out1));

$out2 = F::assoc([]);
$this->assertTrue(is_callable($out2));

$out3 = F::assoc([], 27);
$this->assertTrue(is_callable($out3));

$out4 = F::assoc([], 27, "age");
$this->assertEquals(["age" => 27], $out4);
```

This library also supports placeholder arguments:

```
use FPHP\FPHP as F;

$out1 = f::assoc([], F::__(), "age");
$this->assertTrue(is_callable($out1));
$out2 = $out1(27);
$this->assertEquals(["age" => 27], $out2);
```

### Support for arrays, associative arrays, and objects

The following test cases demonstrate standard usage, including the use of partial application.
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
### Laziness

This library's core functions support laziness when applied to iterators and generators. Given the following transformation function:

```
$count = 0;
$fnTransform = F::pipe(
    F::map(function($v) use(&$count) {
        $count++;
        return $v * 2;
    }),
    F::take(3)
);
```

When the function is applied to an array or object the result is evaluated eagerly - **map** gets called 5 times even though we only want 3 values from the result, and further, the output **$actAssocArr** is calculated straight away, even though it hasn't been used yet.

```
$count = 0;
$assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$actAssocArr = $fnTransform($assocArr);
$this->assertEquals(5, $count);
$this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $actAssocArr);
$this->assertEquals(5, $count);
```
When the function is applied to an iterator or generator, the function evaluates lazily. Although the transform function is called to produce **$actGen**, **$actGen** only gets calculated when it is actually used in the **iterator_to_array function**. Further, **map** only gets called 3 times (as opposed to once for every element in the array example above).

```
$count = 0;
$gen = fn() => yield from ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$actGen = $fnTransform($gen());
$this->assertEquals(0, $count);
$this->assertEquals(["a" => 2, "b" => 4, "c" => 6], iterator_to_array($actGen));
$this->assertEquals(3, $count);
```
### Transducers ###

This library also supports transducers, which allow for lazy evaluation when transforming arrays and objects. The evaluation of the function call is performed immediatly (unlike laziness with iterators and generators, as shown above), however it is generated through backwards propogation this time:
In the example below, $out is fully calculated when the call to **transduce** finishes, however **map** only gets called 3 times, like in the example above with iterators and generators.
```
$count = 0;
$fnTransformT = F::pipe(
    F::mapT(function($v) use(&$count) {
        $count++;
        return $v * 2;
    }),
    F::takeT(3)
);
$assocArr = ["a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5];
$out = F::transduce($fnTransformT, F::assoc(), [], $assocArr);

$this->assertEquals(3, $count);
$this->assertEquals(["a" => 2, "b" => 4, "c" => 6], $out);
$this->assertEquals(3, $count);
```
