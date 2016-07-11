<?php

use Symfony\Component\ClassLoader\Psr4ClassLoader;

########################################################### Prepare

error_reporting(E_ALL);

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/../vendor/autoload.php';

$loader = new Psr4ClassLoader;
$loader->addPrefix('Chernozem\\', '../src');
$loader->register();

########################################################### Basics

$suite = new MiniSuite\Suite('Basics');

$chernozem = new Chernozem\Container();
$chernozem->set('foo', 'bar');

$suite->expects('set()/get()')
	  ->that($chernozem->get('foo'))
	  ->equals('bar');

$chernozem[] = 72;

$suite->expects('set() : with no key')
	  ->that($chernozem->get(0))
	  ->equals(72);

$key = new Stdclass();
$chernozem->set($key, 'bahamut');

$suite->expects('set()/get() : object as key')
	  ->that($chernozem->get($key))
	  ->equals('bahamut');

$suite->expects('get() : not found')
	  ->that(function() use($chernozem) {
		  $chernozem->get('bar');
	  })
	  ->throws('Chernozem\NotFoundException');

$suite->expects('has() : exists')
	  ->that($chernozem->has('foo'))
	  ->equals(true);

$suite->expects('has() : does not exist')
	  ->that($chernozem->has('bar'))
	  ->equals(false);

$chernozem->remove(0);

$suite->expects('remove()')
	  ->that($chernozem->has(0))
	  ->equals(false);

$suite->expects('remove() : not found')
	  ->that(function() use($chernozem) {
		  $chernozem->remove(0);
	  })
	  ->throws('Chernozem\NotFoundException');

$chernozem['final'] = 'fantasy';

$suite->expects('set/get : ArrayAccess')
	  ->that($chernozem['final'])
	  ->equals('fantasy');

$suite->expects('has : ArrayAccess')
	  ->that(isset($chernozem['final']))
	  ->equals(true);

unset($chernozem['final']);

$suite->expects('remove : ArrayAccess')
	  ->that(isset($chernozem['final']))
	  ->equals(false);

$fruits = array(
	'banana' => 'yellow',
	'strawberry' => 'red',
	'lemon' => 'green'
);
$chernozem = new Chernozem\Container($fruits);

$suite->expects('constructor/toArray()')
	  ->that($chernozem->toArray())
	  ->equals($fruits);

$chernozem->clear();

$suite->expects('clear()')
	  ->that($chernozem->toArray())
	  ->equals(array());

$chernozem = new Chernozem\Container(array('final_fantasy' => 7));
$chernozem->setFinalFantasy(8);

$suite->expects('set/get : methods')
	  ->that($chernozem->getFinalFantasy())
	  ->equals(8);

########################################################### Loops

$suite = new MiniSuite\Suite('Loops');

$chernozem = new Chernozem\Container($fruits);
$values = array();
foreach($chernozem as $key => $value) {
	$values[$key] = $value;
}

$suite->expects('foreach()')
	  ->that($chernozem->toArray())
	  ->equals($fruits);

$suite->expects('count()')
	  ->that(count($chernozem))
	  ->equals(3);

########################################################### Inflectors

$suite = new MiniSuite\Suite('Inflectors');

$value = new Chernozem\Value('hello');
$value->addInputInflector(function($value) {
	if(strlen($value) > 8) {
		throw new Exception();
	}
	return $value;
});
$value->addOutputInflector(function($value) {
	return strtoupper($value);
});

$suite->expects('getValue()/addOutputInflector()')
	  ->that($value->getValue())
	  ->equals('HELLO');

$suite->expects('getRawValue()')
	  ->that($value->getRawValue())
	  ->equals('hello');

$suite->expects('setValue()/addInputInflector() : invalid')
	  ->that(function() use($value) {
		  $value->setValue('wonderful');
	  })
	  ->throws();

$value->setValue('bip');

$suite->expects('setValue()/addInputInflector() : valid')
	  ->that($value->getValue())
	  ->equals('BIP');

$chernozem->setter('banana', function($value) {
	if(strlen($value) > 8) {
		throw new Exception();
	}
	return $value;
});
$chernozem->getter('banana', function($value) {
	return strtoupper($value);
});

$suite->expects('setter()')
	  ->that(function() use($chernozem) {
		  $chernozem['banana'] = 'wonderful';
	  })
	  ->throws();

$suite->expects('getter()')
	  ->that($chernozem['banana'])
	  ->equals('YELLOW');

########################################################### Factory closures

$suite = new MiniSuite\Suite('Factory closures');

class Factory { protected $a = 0; public function get() { return ++$this->a; } }

$chernozem = new Chernozem\Container();
$chernozem['factory'] = $chernozem->factory(function($chernozem) use($suite) {
	$suite->expects('factory() : passed container')
		  ->that($chernozem)
		  ->isInstanceOf('Chernozem\Container');
	return new Factory();
});

$suite->expects('factory() : first get')
	  ->that($chernozem['factory']->get())
	  ->equals(1);

$suite->expects('factory() : second get')
	  ->that($chernozem['factory']->get())
	  ->equals(1);

unset($chernozem['factory']);
$chernozem['factory'] = $chernozem->service(function($chernozem) use($suite) {
	$suite->expects('service() : passed container')
		  ->that($chernozem)
		  ->isInstanceOf('Chernozem\Container');
	return new Factory();
});

$suite->expects('service() : first get')
	  ->that($chernozem['factory']->get())
	  ->equals(1);

$suite->expects('service() : second get')
	  ->that($chernozem['factory']->get())
	  ->equals(2);

########################################################### Type hinting

$suite = new MiniSuite\Suite('Type hinting');

$chernozem = new Chernozem\Container(array(
	'int' => null,
	'integer' => null,
	'float' => null,
	'double' => null,
	'bool' => null,
	'boolean' => null,
	'string' => null,
	'array' => null,
	'class' => null,
	'resource' => null,
));
$chernozem->hint('int', 'int');
$chernozem->hint('integer', 'integer');
$chernozem->hint('float', 'float');
$chernozem->hint('double', 'double');
$chernozem->hint('bool', 'bool');
$chernozem->hint('boolean', 'boolean');
$chernozem->hint('string', 'string');
$chernozem->hint('array', 'array');
$chernozem->hint('class', 'Factory');
$chernozem->hint('resource', 'resource');

$suite->expects('int : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['int'] = 1.72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('int : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['int'] = 72;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('integer : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['integer'] = 1.72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('integer : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['integer'] = 72;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('float : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['float'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('float : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['float'] = 1.72;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('double : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['float'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('double : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['float'] = 1.72;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('bool : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['bool'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('bool : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['bool'] = true;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('boolean : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['boolean'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('boolean : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['boolean'] = true;
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('string : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['string'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('string : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['string'] = 'string';
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('array : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['array'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('array : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['array'] = array();
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('class : fail')
	  ->that(function() use($chernozem) {
		  $chernozem['class'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

$suite->expects('class : pass')
	  ->that(function() use($chernozem) {
		  $chernozem['class'] = new Factory();
	  })
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('unsupported type')
	  ->that(function() use($chernozem) {
		  $chernozem['resource'] = 72;
	  })
	  ->throws('Chernozem\ContainerException');

########################################################### Read only values

$suite = new MiniSuite\Suite('Read only values');

$chernozem = new Chernozem\Container(array('foo' => 'bar'));
$chernozem->readonly('foo');

$suite->expects('readonly()')
	  ->that(function() use($chernozem) {
		  $chernozem['foo'] = 'test';
	  })
	  ->throws('Chernozem\ContainerException');

########################################################### Service providers

$suite = new MiniSuite\Suite('Service providers');

class MyService implements Chernozem\ServiceProviderInterface {
	public function register(Interop\Container\ContainerInterface $container) {
		$container['foo'] = 'bar';
	}
}

$chernozem = new Chernozem\Container();
$chernozem->register(new MyService);

$suite->expects('register()')
	  ->that($chernozem['foo'])
	  ->equals('bar');

########################################################### Delegate container

$suite = new MiniSuite\Suite('Delegate container');

$chernozem = new Chernozem\Container();
$chernozem2 = new Chernozem\Container();
$chernozem2['foo'] = 'bar';
$chernozem->delegate($chernozem2);

$chernozem['factory'] = $chernozem->factory(function($container) use($suite) {
	$suite->expects('factory()')
		  ->that(function() use($container) {
			  $container['foo'];
		  })
		  ->doesNotThrow();
});
$chernozem['factory'];

$chernozem['service'] = $chernozem->service(function($container) use($suite) {
	$suite->expects('service()')
		  ->that(function() use($container) {
			  $container['foo'];
		  })
		  ->doesNotThrow();
});
$chernozem['service'];

########################################################### Composite container

$suite = new MiniSuite\Suite('Composite container');

$chernozem = new Chernozem\Container();
$chernozem['bar'] = 'foo';
$chernozem2 = new Chernozem\Container();
$chernozem2['foo'] = 'bar';
$composite = new Chernozem\Composite(array($chernozem));
$composite->add($chernozem2);

$suite->expects('has() : first container')
	  ->that($composite->has('bar'))
	  ->equals(true);

$suite->expects('has() : second container')
	  ->that($composite->has('foo'))
	  ->equals(true);

$suite->expects('has() : not found')
	  ->that($composite->has('pwet'))
	  ->equals(false);

$suite->expects('get() : first container')
	  ->that($composite->get('bar'))
	  ->equals('foo');

$suite->expects('get() : second container')
	  ->that($composite->get('foo'))
	  ->equals('bar');