Chernozem 4.0
=============

Chernozem is an advanced dependency injection container originally based on Pimple.

For basic documentation and fundamentals comprehension, please take a look at https://github.com/fabpot/pimple

Differences from Pimple
-----------------------

- `share()` method was renamed to `persist()`
- closures are __not__ services by default
- multimensionnal arrays support
- iteration support with foreach
- complete serialization
- filters support

Important version remarks
-------------------------

- 0.3.0: closures are not longer set as services by default
- 4.0: hinting, locking, setter and getter support was replaced by filters

Service
-------

A service is a closure that will be executed when retrieved.

    $container=new Chernozem;
    $container['closure']=function(){return 'hop!';};
    // The printed value is a closure
    var_dump($container['closure']);
    // The printed value is a string
    $container->service('closure');
    var_dump($container['closure']);

Multidimensionnal arrays
------------------------

Chernozem supports array chaining.

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

Filters
-------

Filters are executed when a value is set. Here's what we can do with such a functionnality:

Type-hinting:

    $container->filter('fruits',function($value){
        if(!is_array($value)){
            throw new Exception("Expected an array");
        }
        return $value;
    });

Locking:

    $container->filter('fruits',function($value) use($container){
        return $container['fruits'];
    });

As you can see, filters are very flexibles and can be used for many beahviors.

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the serialize.php file, which contains the serialize functions for closures. This file is hosted at https://github.com/pyrsmk/LumyFunctions.

Last words
----------

Chernozem is also shipped with a toArray() method which convert all Chernozem objects in there.
