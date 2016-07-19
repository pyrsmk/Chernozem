Chernozem 4.1.2
===============

Chernozem is an advanced dependency injection container based on `ArrayAccess`. It has been primarily designed to be extended by another class, so it takes care of any option for you. But it can also be used as a simple container.

Concretely, Chernozem can register any value you want and create services. Services are simple closures, used to initialize objects and return their instance. It's really useful for, per example, loading a logger when we need it, with all required options and dependencies it could need.

If you want to learn more about dependency injection, Fabien Potencier has written an article about that : http://fabien.potencier.org/article/11/what-is-dependency-injection.

Features
--------

- interoperability with [container-interop](https://github.com/container-interop/container-interop)
- factory closures
- inflectors
- delegate containers
- service providers
- type hinting
- and more...

Note about the v4
-----------------

Chernozem has been completely rewritten and the API has changed. Please read the whole documentation before upgrading from v3.

Installing
----------

```
composer require pyrsmk/chernozem
```

Basics
------

Let's see quickly how it works :

```php
$chernozem = new Chernozem\Container();

// Set a value
$chernozem['foo'] = 72;

// Get a value
echo $chernozem['foo'];

// Test a value
if(isset($chernozem['foo'])) {
	// 'foo' value exists
}

// Remove a value
unset($chernozem['foo']);
```

Alternatively, and for interoperability, you can access to Chernozem with this API :

```php
// Set a value
$chernozem->set('foo', 72);

// Get a value
echo $chernozem->get('foo');

// Test a value
if($chernozem->has('foo')) {
	// 'foo' value exists
}

// Remove a value
$chernozem->remove('foo');
```

Or also with this API :

```php
// Set a value
$chernozem->setFoo(72);

// Get a value
echo $chernozem->getFoo();
```

You can instantiate Chernozem with some values too :

```php
$chernozem = new Chernozem\Container(array(
	'foo' => 72,
	'bar' => 33
));
```

You can add values to the container without specifying keys (like with a normal array) :

```php
$chernozem[] = 'foo';
```

Chernozem also supports objects as keys :

```php
$myclass = new Stdclass();
$chernozem[$myclass] = 72;
// Print '72'
echo $chernozem[$myclass];
```

If you need to clear all your container values, do :

```php
$chernozem->clear();
```

Factory closures
----------------

With factory closures you can create services. The `factory()` method creates a service that will always return a new instance of the service :

```php
$chernozem['some_service'] = $chernozem->factory(function($chernozem) {
	return new Some_Service();
});
```

The `service()` method creates a service that will return the same instance of itself :

```php
$chernozem['some_service'] = $chernozem->service(function($chernozem) {
	return new Some_Service();
});
```

Service providers
-----------------

Service providers are a way to organize and reuse your services with classes. Let's see how we write a service provider :

```php
class MyService implements Chernozem\ServiceProviderInterface {

	public function register(Interop\Container\ContainerInterface $container) {
		$container['some_service1'] = new Some_Service();
		$container['some_service2'] = new Another_Service();
		$container['an_option'] = 'my@email.com';
	}

}
```

Service providers are run directly when they are registered, with :

```php
$chernozem->register(new MyService());
```

Type hinting
------------

Type hinting is a cool feature of Chernozem that let you define the type of a value in the container. It avoids issues in your application because it could happen that a wrong type is set.

```php
// Set a list of fruits
$chernozem['fruits'] = array('apple', 'banana', 'pear');
// Set type hinting
$chernozem->hint('fruits', 'array');
// Oops! Wrong type!
$chernozem['fruits'] = 72;
```

The following basic types are supported :

- boolean/bool
- integer/int
- float/double
- string
- array
- any class name

Read only values
----------------

You can mark your values as read only if needed with :

```php
// Set a 'mailer' service
$chernozem['mailer'] = $chernozem->service(function($chernozem) {
	return new Mailer();
});
// Mark as read onyl
$chernozem->readonly('mailer');
```

Inflectors
----------

All previous features are available thanks to inflectors. Inflectors are a way to alter a value when it's set or got. Note that setter inflectors are usually used for data validation, and getter inflectors for data filtering. For the next example, let's say we have previously set some random service that we don't want to be overwritten further in our application, and we want to run some actions each time the service is retrieved :

```php
$chernozem->setter('foo_service', function($service) {
	throw new Exception("'foo_service' is already set!");
	return $service; // Never run, it's only for the example
});

$chernozem->getter('foo_service', function($service) {
	$service->executeSomeAction();
	$service->executeAnotherAction();
	return $service;
});
```

Now, setting 'foo_service' will throw an exception and each time 'foo_service' is retrieved, two actions will be run on our service. It's that simple!

Delegate and composite container
--------------------------------

A delegate container is a container that will be used to load dependencies for services. You may see that `factory()` and `service()` pass Chernozem as parameter. It's used for the service to load some dependencies on the container. But in big applications, you could deal with many different containers and your dependencies could be on another container than Chernozem.

```php
$delegate_container = new SomeVendor\Container();

// ... some stuff ...

$chernozem->delegate($delegate_container);

$chernozem['some_service'] = $chernozem->service(function($delegate_container) {
	$some_service = new Some_Service();
	// Load an option that is registered in the delegate container
	$some_service->setOption('some_option', $delegate_container->get('some_option'));
	return $some_service;
});
```

If you have a lot of containers to manage,, Chernozem has a `Composite` class to take care of that :

```php
// Instantiate containers
$container1 = new SomeVendor\Container();
$container2 = new AnotherVendor\Container();

// ... some stuff ...

// Add containers to the composite container
$composite = new Chernozem\Composite();
$composite->add($container1);
$composite->add($container2);
```

Now that all your containers are registered, you can get a value as always :

```php
echo $composite['foo'];
```

Calling `foo` on the composite class will get the first `foo` value found in the registered containers. You can verify if a key exist too :

```php
if(isset($composite['foo'])){
	// the 'foo' key exists
}
```

Note that the `Composite` class is based on [container-interop](https://github.com/container-interop/container-interop), and it also supports a `delegate()` method so you can chain things, if needed.

Loops
-----

Chernozem container also implements `Iterator` and `Countable`. That means that you can iterate over the container and count how many elements are in it.

```php
echo count($chernozem);
```

```php
foreach($chernozem as $id => $value) {
	var_dump($value);
}
```

Alternative ways to get data
-----------------------------

You can retrieve all container values with :

```php
$values = $chernozem->toArray();
```

If you need to get a raw value that has not been modified by inflectors, like the base closure of a service, call `raw()` :

```php
$chernozem['some_service'] = $chernozem->service(function($c) {
	// do stuff
});

$closure = $chernozem->raw('some_service');
```

Chaining arrays
---------------

You can't chain arrays to modify or retrieve a value with Chernozem, this is due to a PHP limitation with `ArrayAccess`. The basic way to handle this case is :

```php
// Get 'foo' array
$foo = $chernozem['foo'];
// Add 'bar' value to the array
$foo['bar'] = 42;
// Update 'foo' value
$chernozem['foo'] = $foo;
```

But to simplify this, you can declare a `Chernozem\Container` object instead of an array as your container value, then you'll be able to chain things :

```php
$chernozem['foo'] = new Chernozem\Container(array('bar' => 72));
// Print '72'
echo $chernozem['foo']['bar'];
$chernozem['foo']['bar'] = 42;
// Print '42'
echo $chernozem['foo']['bar'];
```

Last notes
----------

If you want to overwrite a factory closure (created with `factory()` or `service()`), you must unset the value before setting it :

```php
$chernozem['service'] = $chernozem->service(function() {
	// First service
});

unset($chernozem['service']);

$chernozem['service'] = $chernozem->service(function() {
	// New service
});
```

License
-------

Chernozem is published under the [MIT license](http://dreamysource.mit-license.org).
