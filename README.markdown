Chernozem 0.1.1
===============

Chernozem is an advanced dependency injection container originally based on Pimple.

For basic documentation and fundamentals comprehension, please take a look at https://github.com/fabpot/pimple

Multidimensionnal support
-------------------------

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

    foreach($container as $fruit=>$color){
        echo "$fruit : $color";
    }

Writing lock
------------

    $container->lock('fruits');
    // Will throw an exception
    $container['fruits']=72;

Type-hinting
------------

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

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the functions.php file, which contains the serialize functions for closures. 
