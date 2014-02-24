Chernozem 2.5.0
===============

Chernozem is an advanced dependency injection container based on the `ArrayAccess` PHP core object.

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection.

With dependency injection containers, you can inject values (configure your object) with an array style. It is very helpful because extending those classes will drop much of your setters/getters and will provide a robust implementation. For a good example, take a look to the [Lumy](https://github.com/pyrsmk/Lumy) framework project, which has heavy Chernozem integration.

Basics
======

There are two behaviors into Chernozem : `container` and `properties`. The `container` behavior can stock values regardless of what they are and what they mean. The `properties` behavior lets you define existing object properties.

You can modify those behaviors by set, in a Chernozem child constructor, some internal properties :

```php
    class SampleClass extends Chernozem{

        public function __construct(){
            // False to disable properties behavior (default : true)
            $this->__properties=true;
            // False to disable container behavior (default : true)
            $this->__container=true;
            // False to make Chernozem non traversable by foreach() (default : true)
            $this->__traversable=true;
            // True to not send an exception when retrieving a non existing value (default : false)
            $this->__nullable=false;
            // True to lock, the object will not be editable anymore (default : false)
            $this->__locked=false;
        }

    }
```

You can pass an array or a `Traversable` object to the constructor to add values directly to your container or set your properties :

```php
    $container=new SampleClass(array(
        'foo'       => false,
        'bar'       => 0.758,
        'foobar'    => function(){
            return 'something';
        }
    ));
    // Print 0.758
    echo $container['bar'];
```

If you want, you can instantiate Chernozem itself and use it directly. Of course, only the `container` behavior will be support :

```php
    $container=new Chernozem(array('some','values'));
    $container['foo']='bar';
    foreach($container as $key=>$value){
        echo "$key : $value\n";
    }
    /*
        This will echo :

        0 : some
        1 : values
        foo : bar
    */
```

Container behavior
==================

```php
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
```

As you see, it supports all basic array operations, numeric keys and even the [] syntax:

```php
    $container[]=72;
```

Furthermore, you can use object keys:

```php
    $object=new stdClass();
    $container[$object]=72;
    // Print 72
    echo $container[$object];
```

Chernozem implements two other interfaces: `Countable` and `Iterator`. That means you can use the `count()` PHP function to know how many values are into the container and the `foreach()` structure control to iterate over your Chernozem object.

```php
    // Print the number of elements in the container
    echo count($container);
    // Iterate
    foreach($container as $key=>$value){
        echo $value;
    }
```

If you want to retrieve all values as an array, use the `toArray()` method:

```php
    var_dump($container->toArray());
```

Properties behavior
===================

In this mode, you can use properties from your objects with the following rules:

- `$var` is editable
- `$_var` is not but is still accessible
- `$__var` should be use for specific internal variables
- only strings are allowed as keys

With the properties mode, you can't unset values or use `count()` and `foreach()`. With the `toArray()` method, no property will be returned : this is just compatible with the container mode.

But here's the most interesting part!

```php
    class SampleClass{

        protected $foo='blahblah';
        protected $_bar=72;
        protected $__foobar;

    }
```

```php
    // Print 'blahblah'
    echo $container['foo'];
    // Print 'lollipop'
    $container['foo']='lollipop';
    echo $container['foo'];
    // Print '72'
    echo $container['bar'];
    // This will throw an exception
    $container['bar']=230;
    // This too, since $__foobar is not accessible at all
    echo $container['foobar'];
```

Services
========

Closure values can be set as services. That means the closure will be automatically launched when the user retrieves it. It's really useful to initialize objects on-demand to save resources. When initialized, the object will be saved as the real value of the specified key.  Here's a quick example with [Twig](http://twig.sensiolabs.org) :

```php
    // Set initialization closure for Twig
    $container['twig']=function(){
        require_once '/path/to/lib/Twig/Autoloader.php';
        Twig_Autoloader::register();
        $twig=new Twig_Environment(
            new Twig_Loader_Filesystem('/path/to/templates'),
            array('cache'=>'/path/to/compilation_cache')
        );
        return $twig;
    };
    // Define that closure as a service
    $container->service('twig');
```

Now, when you'll access to the `twig` value you won't have the closure itself, but the initialized Twig object :

```php
    // Render a template
    $container['twig']->render('index.tpl');
```

If you want to remove the `twig` closure as a service, just do :

```php
    $container->unservice('twig');
```

Chaining arrays
===============

You can't chain arrays to modify or retrieve a value with Chernozem class, this is due to a PHP limitation with `ArrayAccess`. The basic way to handle this case is :

```php
    // Retrieve 'foo' array
    $foo=$container['foo'];
    // Add 'bar' to the array
    $foo['bar']=42;
    // Update 'foo' value
    $container['foo']=$foo;
```

But to simplify this, you can declare a Chernozem object instead of an array, then you'll be able to chain :

```php
    $container['foo']=new Chernozem(array('some','values'));
    $container['foo']['bar']=42;
```

Last remarks
============

- Please note that `properties` behavior having priority over `container` behavior. That means if you insert a value with a key that is not managed by the properties behavior, then that value will be inserted to the container (but if that container is disabled, Chernozem will throw an exception). As well, if you retrieve a value that exists with both behaviors (because of inserting it internally) then the returned value will be the `properties` one.
- For performance purpose, from a Chernozem child, please use `$this->__values['foo']` to access to the container rather than `$this['foo']`.

License
=======

Chernozem is published under the MIT license. Feel free to fork it ;)
