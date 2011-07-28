Chernozem 0.5.1
===============

Chernozem is an advanced dependency injection container originally based on Pimple (https://github.com/fabpot/pimple).

Differences from Pimple
-----------------------

- closures are __not__ services by default
- multimensionnal arrays
- iteration
- complete serialization
- filters support
- values count
- values search

Important version remarks
-------------------------

- 0.3.0: closures are not longer set as services by default
- 0.4.0: persistance, hinting, locking, setter and getter support was replaced by filters

What the hell is that?
----------------------

Dependency injection is a design pattern to make better encapsulation of external objects into another object. Fabien Potencier has written an article about that, I advise you to read it: http://fabien.potencier.org/article/11/what-is-dependency-injection. Chernozem is an array dependency injection container. It means that it will act as an array (even it's an object). It is very helpful because extending it will drop much of your setters/getters and will provide a strong implementation which will be the same across all your classes.

Basics
------

Chernozem supports all basic array operations.

    $container=new Chernozem;
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

Numeric keys are also supported, even the `[]` syntax:

    $container[]=72;

You can also pass an array the constructor to add values directly into your container:

    $container=new Chernozem(array(
        'foo'       => false,
        'bar'       => 0.758,
        'foobar'    => function(){
            return 'something';
        }
    ));

Other useful functions
----------------------

You can get the number of values of your container:

    if(count($container)==2{
        echo 'You really have two values!';
    }

And search keys:

    // Will return 'bar' (from the example above)
    $container->search(0.758);

Multidimensionnal arrays
------------------------

Chernozem supports array chaining by creating a Chernozem for each arrays:

    $container=new Chernozem;
    $container['fruits']=new array(
        'kiwi'          => 'green',
        'strawberry'    => 'red',
        'banana'        => 'yellow'
    );
    // Print 'red'
    echo $container['fruits']['strawberry'];

Iteration
---------

It's quite simple to iterate over your container:

    foreach($container as $fruit=>$color){
        echo "$fruit : $color";
    }

Array conversion
----------------

Chernozem is also shipped with a complete `toArray()` conversion, all Chernozem objets in there will be converted:

    var_dump($container->toArray());

Services
--------

A service is a closure that will be executed when retrieved:

    $container['closure']=function($chernozem){return $chernozem;};
    // The printed value is a closure
    var_dump($container['closure']);
    // The printed value is the container itself
    $container->service('closure');
    var_dump($container['closure']);

As you can see, Chernozem will pass its instance as parameter to your closure.

Filters
-------

Filters make your life easier by extending Chernozem functionnalities for one value. Please keep in mind that they are executed when a value is set for performance purposes. Here's what we can do with them:

Type-hinting:

    $container->filter('fruits',function($key,$value){
        if(!is_array($value)){
            throw new Exception("Expected an array");
        }
        return $value;
    });

Locking:

    $container->filter('fruits',function($key,$value) use($container){
        return $container['fruits'];
    });

Persistence for services:

    // Add persistence filter
    $container->filter('foo',function($key,$closure){
        if($closure instanceof Closure){
            $closure=function($chernozem) use($closure){
                static $persistent;
                if($persistent===null){
                    $persistent=$closure($chernozem);
                }
                return $persistent;
            };
        }
        return $closure;
    });
    // Create the service
    $container['foo']=function($chernozem){
        return time();
    };
    $container->service('foo');

The service will always return the same time :

As you can see, filters are very flexibles and can be used for many behaviors. Nevertheless, above examples are directly included to the Chernozem project into the `filters.php` file. To be able to use them into your projects, you can act as follow:

    include('Chernozem/filters.php');
    global $chernozem_lock;
    $chernozem_lock($container,'foo');

And your `foo` value will be locked. Just take a look at `filters.php` to know what are the name and use of the built-in filters. But, be careful, you __can't__ add many filters for the same value.

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the serialize.php file, which contains the serialize functions for closures. This file is hosted at https://github.com/pyrsmk/LumyFunctions.
