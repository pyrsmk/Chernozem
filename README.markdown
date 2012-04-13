Chernozem 2.0.1
===============

Chernozem is a dependency injection container.

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection.

With dependency injection containers, you can inject values (configure your object) with an array style. It is very helpful because extending thoses classes will drop much of your setters/getters and will provide a robust implementation. For a good example, let's take a look to the [Lumy](https://github.com/pyrsmk/Lumy) framework project, which has heavy Chernozem integration.

Basics
======

There're two behaviors into Chernozem: container mode and properties mode. The container mode can stock values regardless of what they are and what they mean. And the properties mode lets you define pre-existing object properties.

You can modify those behaviors by set, in a Chernozem child constructor, some internal properties.

    public function __construct(){
        // False to disable properties behavior
        $this->__properties=true;
        // False to disable container behavior
        $this->__container=true;
        // False to make Chernozem non traversable by foreach()
        $this->__traversable=true;
    }

All those internal variables are enabled by default.

Container behavior
==================

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

As you see, it supports all basic array operations, numeric keys too and even the [] syntax:

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

Chernozem implements two other interfaces: `Countable` and `Iterator`. That means you can use the `count()` PHP function to know how many values are into the container and the `foreach()` structure control to iterate over your Chernozem objects.

If you want to retrieve all values as an array, use the `toArray()` method:

    var_dump($container->toArray());

One last thing. For performance purpose, from a Chernoze child, please use `$this->__values['foo']` to access to the container rather than `$this['foo']`.

Properties behavior
===================

In this mode, you can use properties from your objects with the following rules:

- `$var` is editable
- `$_var` is not but is still accessible
- `$__var` should be use for specific internal variables

Only strings are allowed as keys. But here's the most interesting part:

    // Access to '$foo' property
    echo $container['foo'];
    // Access to locked $_bar property
    echo $container['bar'];
    // Set a locked property will throw an exception
    $container['bar']=72;
    // Will throw an exception since $__foobar is out of the scope
    echo $container['foobar'];

Last remarks
============

You can't chain arrays to modify or retrieve a value with Chernozem class, this is due to a PHP limitation with `ArrayAccess`. So, you''ll must do:

    $foo=$c['foo'];
    $foo['bar']=42;
    $c['foo']=$foo;

License
=======

Chernozem is published under the MIT license. Feel free to fork it ;)
