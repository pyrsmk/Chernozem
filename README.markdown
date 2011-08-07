Chernozem I.I
=============

Chernozem is an advanced dependency injection container originally based on Pimple (https://github.com/fabpot/pimple).

Differences from Pimple
-----------------------

- closures are __not__ services by default
- iteration
- complete serialization
- filters support
- values count
- incrementational setter support
- object key type support

Important version changes
-------------------------

- 0.3.0: closures are not longer set as services by default
- 0.4.0: persistance, hinting, locking, setter and getter support was replaced by filters
- 0.6.0: service() was dropped in favor of filter implementation
- I.I: for performance purposes (about +69%) multidimensional arrays support and search() method were removed

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

Please note that you can pass an object as key:

    $container[new stdClass()]=72;

This example is not that obvious but, as you can see, you're able to identify your values by an object.

But, be careful that Chernozem is just a container and does not support multidimensional arrays syntax. As `$c['foo']=42` will work, `$c['foo']['bar']=42` won't. You'll must act with:

    $foo=$c['foo'];
    $foo['bar']=42;
    $c['foo']=$foo;

It's a technical PHP limitation. In its later versions, Chernozem supported multdimensional arrays syntax but it required to create a new instance for each array in the container and as said previsouly it was 69% slower. Chernozem is a raw material object, so performance goal win ^^

Countable
---------

You can get the number of values of your container:

    if(count($container)==2{
        echo 'You really have two values!';
    }

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

Filters
-------

Filters make your life easier by extending Chernozem setters, getters and unsetters. Here's what we can do with them:

Type-hinting:

    $container->filter(
        'fruits',
        function($key,$value){
            if(!is_array($value)){
                throw new Exception("Expected an array");
            }
            return $value;
        },
        // Not really needed since the default value is FILTER_SET
        $container::FILTER_SET
    );

Locking:

    $container->filter(
        'fruits',
        function($key,$value) use($container){
            return $container['fruits'];
        }
    );
    
    $container->filter(
        'fruits',
        function($key,$value){
            return false;
        },
        $container::FILTER_UNSET
    );


As you can see, filters are very flexibles and can be used for many behaviors. As it was said above, there are only 3 filter types: `FILTER_SET`, `FILTER_GET` and `FILTER_UNSET`. Set and get filters work on the same way: a value, and its corresponding key, is passed and a value must be returned. For a set filter, the new value will overwrite the original one. For a get filter, it will just return the closure's returned value. Unset filters are a bit different: keys and values are well passed to the closure, but the returned value must be boolean. If the value is allowed to be removed, true must be returned, false otherwise.

To save your time, Chernozem is shipped with many useful filters as functions. To be able to use them into your projects, you can act as follow:

    include('Chernozem/filters.php');
    chernozem_lock($container,'foo');

And your `foo` value will be locked.

Just take a look at `filters.php` to know what are the name and use of the built-in filters. But, be careful, you __can't__ add many filters for the same filter type.

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the `serialize.php` file, which contains the serialization functions for closures. This file is hosted at https://github.com/pyrsmk/LumyFunctions.
