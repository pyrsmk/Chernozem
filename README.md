Chernozem 3.0.7
===============

Chernozem is an advanced dependency injection container based on the `ArrayAccess` PHP core object, like [Pimple](http://pimple.sensiolabs.org/), but with different features.

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection.

With dependency injection containers, you can inject values with an array writing and so configure your object. It is very helpful because extending those containers will drop much of your setters/getters and provide a robust implementation. Many tools use those containers like the [Lumy](https://github.com/pyrsmk/Lumy) or [Silex](http://silex.sensiolabs.org/) framework.

Installing
----------

Pick up the source or install it with [Composer](https://getcomposer.org/) :

```json
composer require pyrsmk/chernozem
```

Basics
------

You can pass an array or a `Traversable` object to the constructor to add values directly to your container or set some of your internal properties (we'll see all this later) :

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

If you want, you can instantiate Chernozem itself and use it directly :

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

The container
-------------

As we said, Chernozem works as a value container. Let's see how :

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

As you can see, it supports all basic array operations, numeric keys and even the `[]` syntax :

```php
$container[]=72;
```

Moreover, you can use objects as keys:

```php
$object=new stdClass();
$container[$object]=72;
// Print 72
echo $container[$object];
```

Chernozem implements two interfaces : `Countable` and `Iterator`. That means you can use the `count()` PHP function to know how many values are into the container and the `foreach()` structure control to iterate over your Chernozem object.

```php
// Print the number of elements in the container
echo count($container);
// Iterate
foreach($container as $key=>$value){
    echo $value;
}
```

And if you want to retrieve all values from the container,you can use the `toArray()` method:

```php
// Will print all registered values
var_dump($container->toArray());
```

Internal object properties
--------------------------

By extending Chernozem, you can modify properties from your objects with the following rules :

- `$var` properties are editable
- `$_var` aren't but are still accessible
- `$__var` properties are not editable neither accessible
- only strings are allowed as keys
- you cannot use `unset`
- properties are not count by the `count()` function
- `foreach()` does not iterate other properties but only over container values
- `toArray()` does not return any property
- properties have priority over container values

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
// This line will print nothing,
// since $__foobar is not accessible at all
// the returned value will be from the container instead from a propery
echo $container['foobar'];
```

Services
--------

Closure values can be set as services. That means the closure will be automatically executed when the user will retrieve it. It's really useful to initialize objects on-demand to save resources.
When initialized, the object will be saved as the real value of the specified key.  Here's a quick example with [Twig](http://twig.sensiolabs.org) :

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
---------------

You can't chain arrays to modify or retrieve a value with Chernozem, this is due to a PHP limitation with `ArrayAccess`. The basic way to handle this case is :

```php
// Retrieve 'foo' array
$foo=$container['foo'];
// Add 'bar' to the array
$foo['bar']=42;
// Update 'foo' value
$container['foo']=$foo;
```

But to simplify this, you can declare a Chernozem object instead of an array as your container value, then you'll be able to chain :

```php
$container['foo']=new Chernozem(array('bar'=>72));
// Print '72'
echo $container['foo']['bar'];
$container['foo']['bar']=42;
// Print '42'
echo $container['foo']['bar'];
```

Last remarks
------------

For performance purpose, if you want to access to container values from a Chernozem child, please use `$this->__chernozem_values['foo']` rather than `$this['foo']`.

License
-------

Chernozem is published under the [MIT license](http://dreamysource.mit-license.org).
