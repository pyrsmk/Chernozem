Chernozem 0.3.0
===============

Chernozem is an advanced dependency injection container originally based on Pimple.

For basic documentation and fundamentals comprehension, please take a look at https://github.com/fabpot/pimple

Differences from Pimple
-----------------------

- `share()` method was renamed to `persist()`
- closures are __not__ services by default
- multimensionnal arrays support
- iteration support with foreach
- can lock variables to prevent future writing accesses
- type-hinting support to prevent bad values
- setter/getter support
- complete serialization

Important version remarks
-------------------------

- since 0.3.0, closures are not set as services by default

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

Persistence
-----------

Persistence permit to closures to not return new values each time they're getted.

    $container=new Chernozem;
    $container['timestamp']=microtime();
    // Persistent closures must be set as services before
    $container->service('timestamp');
    // Persistence
    $container->persist('timestamp');
    // Print the same timestamp twice
    echo $container['timestamp'],"<br>",$container['timestamp'];

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

Writing lock
------------

Forbid future value modifications:

    $container->lock('fruits');
    // Will throw an exception
    $container['fruits']=72;

Type-hinting
------------

Chernozem lets you type-hint the values that will be setted by the user, to get more consistent values anywhere in your code.

    // Only accept integer and float types
    $container->hint('fruits',array('int','float'));
    // Will throw an exception
    $container['fruits']=true;

Here's the valid types:

- int
- integer
- long
- float
- double
- real
- numeric
- bool
- boolean
- string
- scalar
- array
- object
- resource
- callable

It can handles object names too:

    $container->hint('fruits','My\Own\Object');

Setter/getter
-------------

If it's needed to format value before registration, you can add a setter for that value.

    $container->setter('fruits',function($value){
        if($value=='strawberry'){
            $value='STRAWBERRY';
        }
        return $value;
    });

The same, with getters:

    $container->getter('fruits',function($value){
        return strtoupper($value);
    });

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the serialize.php file, which contains the serialize functions for closures. This file is hosted at https://github.com/pyrsmk/LumyFunctions.

Last words
----------

Chernozem is also shipped with a toArray() method which convert all Chernozem objects in there.
