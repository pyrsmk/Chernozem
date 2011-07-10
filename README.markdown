Chernozem 0.2
=============

Chernozem is an advanced dependency injection container originally based on Pimple.

For basic documentation and fundamentals comprehension, please take a look at https://github.com/fabpot/pimple

Differences from Pimple
-----------------------

- `share()` method was renamed to `persist()`
- `protect()` method was renamed to `integrate()`
- multimensionnal arrays support
- iteration support with foreach
- can lock variables to prevent future writing accesses
- type-hinting support to prevent bad values
- setter/getter support
- complete serialization

Persistence
-----------

    $container=new Chernozem;
    $container['timestamp']=microtime();
    $container->persist('timestamp');
    // Print the same timestamp twice
    echo $container['timestamp'],"<br>",$container['timestamp'];

Integration
-----------

    $container=new Chernozem;
    $container['closure']=function(){return 'hop!';};
    // The value is a string
    var_dump($container['closure']);
    // The value is the primitive closure
    $container->persist('closure');
    var_dump($container['closure']);

Multidimensionnal arrays
------------------------

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

Setter/getter
-------------

    $container->setter('fruits',function($value){
        if($value=='strawberry'){
            $value='STRAWBERRY';
        }
        return $value;
    });

    $container->getter('fruits',function($value){
        return strtoupper($value);
    });

Serialization
-------------

By default, closures cannot be serialized. Since Chernozem is a raw material, we've chosen to implement it. But, in order to use serialization, you __must__ include the functions.php file, which contains the serialize functions for closures. 
