# PHP Pipeline

A simple Pipeline implementation for PHP.

## Summary

This package enables you to send a value through a sequence of steps. Each step operates on the return
value of the previous step. Without a pipeline you would end up with deeply nested function calls like this.

```php
fn5(fn4(fn3(fn2(fn1($initialValue)))));
```

Using a pipeline you can transform the above example to this:

```php
Pipeline::pipe($initialValue)
    ->through($fn1)
    ->through($fn2)
    ->through($fn3)
    ->through($fn4)
    ->through($fn5)
    ->run();
```

This makes the order of steps executed a lot clearer and gets rid of the annoying and ugly nesting of function calls.

## Usage

```php
use Sassnowski\Pipeline\Pipeline;

Pipeline::pipe(10)
    ->through(function ($i) { return $i + 10; })
    ->run();
// 11
```

## Building reusable pipelines

The `through($fn)` method does not change the existing Pipeline, but instead returns a new Pipeline instance that includes
the next step. This enables you to reuse parts of a pipeline.

Since seeding the pipeline with an initial value goes against the idea of reusability, an additional method `build()` is defined.
The `build` method does not initialize the pipeline with value. In this case the value has to be provided when calling the `run` method.

```php
$pipeline1 = Pipeline::build()->through(function ($i) { return $i + 1 });

// Use the existing pipeline and simply add on additional steps.
$pipeline2 = $pipeline1->through(function ($i) { return $i * 10; });

// The initial pipeline remains unchanged.
$pipeline1->run(10); // 11

$pipeline2->run(10); // 110
```

## Class-based steps

It is possible to use classes as steps instead of function. All the class has to do is implement the magic `__invoke` method.

```php
class Add10
{
    function __invoke($i)
    {
        return $i + 10;
    }
}

// Somewhere else
Pipeline::pipe(10)
    ->through(new Add10)
    ->run();
// 20
```

This is useful if the execution of a step requires a lot of additional business logic that does not belong inside the object
containing the Pipeline.

## Exceptions

If a step is added to the pipeline that fails the `is_callable` check an `InvalidArgumentException` will be thrown.

```php
Pipeline::pipe(10)->through(10); // InvalidArgumentException
```

If the `build` method was used to create a pipeline, the initial value has to be passed to then `run` method. Failing to do so
will result in a `RuntimeException`.

```
Pipeline::build()->through(function ($i) { return $i + 10; })->run() // RuntimeException
```

## Method aliases

In order to enable a more easily readable syntax when building a pipeline several method aliases for the `through` method exist.

* `firstThrough()`
* `andThen()`

```php
Pipeline::pipe(10)
    ->firstThrough($fn1)
    ->andThen($fn2)
    ->andThen($fn3)
    ->run();
```

## License

MIT