Chernozem 2.3.0
===============

Chernozem is a dependency injection container.

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection.

With dependency injection containers, you can inject values (configure your object) with an array style. It is very helpful because extending those classes will drop much of your setters/getters and will provide a robust implementation. For a good example, let's take a look to the [Lumy](https://github.com/pyrsmk/Lumy) framework project, which has heavy Chernozem integration.

Basics
======

There're two behaviors into Chernozem: container and properties. The container side can stock values regardless of what they are and what they mean. The properties side lets you define pre-existing object properties.

You can modify those behaviors by set, in a Chernozem child constructor, some internal properties (values set are default values):

    class SampleClass extends Chernozem{

        public function __construct(){
            // False to disable properties behavior
            $this->__properties=true;
            // False to disable container behavior
            $this->__container=true;
            // False to make Chernozem non traversable by foreach()
            $this->__traversable=true;
            // True to not send an exception when retrieving a non existing value
            $this->__nullable=false;
            // True to lock, the object will not be editable anymore
            $this->__locked=false;
        }

    }

You can pass an array or a `Traversable` object to the constructor to add values directly into your container (or as properties):

    $container=new SampleClass(array(
        'foo'       => false,
        'bar'       => 0.758,
        'foobar'    => function(){
            return 'something';
        }
    ));
    // Print 0.758
    echo $container['bar'];

Properties behavior having priority over container behavior. That means that if you insert a value with a key that is not managed by the properties behavior, then that value will be inserted to the container (but if that container is disabled, Chernozem will throw an exception). As well, if you retrieve a value that exists with both behaviors then the returned value will be the properties one.

Container behavior
==================

    // Instantiate a Chernozem child
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

Furthermore, you can use object keys:

    $object=new stdClass();
    $container[$object]=72;
    // Print 72
    echo $container[$object];

Chernozem implements two other interfaces: `Countable` and `Iterator`. That means you can use the `count()` PHP function to know how many values are into the container and the `foreach()` structure control to iterate over your Chernozem objects.

    // Print the number of elements in the container
    echo count($container);
    // Iterate
    foreach($container as $key=>$value){
        echo $value;
    }

If you want to retrieve all values as an array, use the `toArray()` method:

    var_dump($container->toArray());

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

With the properties mode, you can't unset values, can't use `count()`, `foreach()`. With `Chernozem#toArray()`, no property will be returned, this is just compatible with the container behavior.

Services
========

Closures (as values) can be setted as services. That means the closure will be automatically launched when the user retrieves it. It's really useful to initialize objects on demand. When initialized, the object will be saved as the real value of the specified key.  Here's a quick example with [Twig](http://twig.sensiolabs.org):

    $container['twig']=function(){
        require_once '/path/to/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();
        $loader=new Twig_Loader_Filesystem('/path/to/templates');
        $twig=new Twig_Environment(
            $loader,
            array('cache'=>'/path/to/compilation_cache')
        );
        return $twig;
    };

Last remarks
============

You can't chain arrays to modify or retrieve a value with Chernozem class, this is due to a PHP limitation with `ArrayAccess`. So, you'll must do:

    $foo=$c['foo'];
    $foo['bar']=42;
    $c['foo']=$foo;

For performance purpose, from a Chernozem child, please use `$this->__values['foo']` to access to the container rather than `$this['foo']`.

Also note that you can pass an object as key:

    $container[$object]=99;
    // Echoes 99
    echo $container[$object];

License
=======

Chernozem is published under the MIT license. Feel free to fork it ;)
