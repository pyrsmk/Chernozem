Chernozem
=========

Chernozem is a collection of dependency injection managers.

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection. Chernozem classes are array dependency injection containers. That means you can inject values (configure your object) with an array style. It is very helpful because extending thoses classes will drop much of your setters/getters and will provide a robust implementation. For a good example, let's take a look to the [Lumy](https://github.com/pyrsmk/Lumy) framework project, which has heavy Chernozem integration.

Notes about the 0.x branch
==========================

Chernozem 1.x is __NOT__ compatible at all with Chernozem 0.x (the last is 0.7.1).

With Chernozem 0.x, serializing and unserializing closures was natively included into the project. From now, you will must do that by yourself with the `serialize.php` class from the [Funktions](https://github.com/pyrsmk/Funktions) project. Also, filters have been dropped.

That said, let's go for some explanations!

Chernozem\Container
===================

Chernozem is shipped with two class which have their own purpose. With `Chernozem\Container`, each value is totally manipulable by the user, contrary to `Chernozem\Properties` where the values are intrinsically linked to the object properties and can be locked to prevent rewrites. Here how `Chernozem\Container` works:

    // Instanciate a Chernozem child
    $container=new SampleClass;
    // Add a value
    $container['foo']='bar';
    // Get a value
    echo $container['foo'];
    // Verify existence
    if(isset($container['foo'])){
        echo 'foo exists';
    }
    // Drop
    unset($container['foo']);
    if(!isset($container['foo'])){
        echo 'foo does not exists';
    }

As you see, it supports all basic array operations. And numeric keys too, even the [] syntax! Awesome!

    $container[]=72;

You can also pass an array or a `Traversable` object to the constructor to add values directly into your container:

    $container=new SampleClass(array(
        'foo'       => false,
        'bar'       => 0.758,
        'foobar'    => function(){
            return 'something';
        }
    ));

Another great functionnality:

    $container[new stdClass()]=72;

This example is not that obvious but, as you can see, you're able to identify your values by an object.

`Chernozem\Container` implements two other interfaces: `Countable` and `Iterator`. That means you can use the `count()` PHP function to know how many values are into the container and the `foreach()` structure control to iterate over your Chernozem objects.

One last thing, `Chernozem\Container` has a `toArray()` method to retrieve all the container values:

    var_dump($container->toArray());

Chernozem\Properties
====================

As it's previously said, `Chernozem\Properties` has its values linked to the child object properties, so its working is very different from its brother. First of all, you _can't_ pass a `Traversable` object to the constructor but just an array:

    $container=new SampleClass(array(
        'foo'       => false,
        'bar'       => 0.758,
        'foobar'    => function(){
            return 'something';
        }
    ));

For the `ArrayAccess` behavior, only strings are allowed as keys. But here's the most interesting part:

    // Access to '$foo' property
    echo $container['foo'];
    // Access to locked $_bar property
    echo $container['bar'];
    // Set a locked property will throw an exception
    $container['bar']=72;

To handle internal variables and to separate them from the `Chernozem\Properties` scope, just prefix their names with some underscores:

    // Will throw an exception since $__foobar is out of the scope
    echo $container['foobar'];

And that's all. And I think you can easily understand why `Chernozem\Properties` doesn't implement `Countable`, `Iterator` or `toArray()`.

Last remarks
============

You can't chain arrays to modify or retrieve a value with Chernozem class, this is due to a PHP limitation with `ArrayAccess` (in fact, Chernozem 0.6 did that but with huge performance costs). So, you will must do:

    $foo=$c['foo'];
    $foo['bar']=42;
    $c['foo']=$foo;
