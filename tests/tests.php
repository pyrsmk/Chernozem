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

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container();
});

$suite->expects('set()/get()')
	  ->that(function($suite) {
		  $suite['chernozem']->set('foo', 'bar');
		  return $suite['chernozem']->get('foo');
	  })
	  ->equals('bar');

$suite->expects('set() : with no key')
	  ->that(function($suite) {
		  $suite['chernozem'][] = 72;
		  return $suite['chernozem']->get(0);
	  })
	  ->equals(72);

$suite->expects('set()/get() : object as key')
	  ->that(function($suite) {
		  $key = new Stdclass();
		  $suite['chernozem']->set($key, 'bahamut');
		  return $suite['chernozem']->get($key);
	  })
	  ->equals('bahamut');

$suite->expects('get() : not found')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']->get('bar');
	  }))
	  ->throws('Chernozem\NotFoundException');

$suite->expects('has() : exists')
	  ->that(function($suite) {
		  $suite['chernozem']->set('foo', 'bar');
		  return $suite['chernozem']->has('foo');
	  })
	  ->equals(true);

$suite->expects('has() : does not exist')
	  ->that(function($suite) {
		  return $suite['chernozem']->has('bar');
	  })
	  ->equals(false);

$suite->expects('remove()')
	  ->that(function($suite) {
		  $suite['chernozem']->set('foo', 'bar');
		  $suite['chernozem']->remove('foo');
		  return $suite['chernozem']->has('foo');
	  })
	  ->equals(false);

$suite->expects('remove() : not found')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']->remove('foo');
	  }))
	  ->throws('Chernozem\NotFoundException');

$suite->expects('set/get : ArrayAccess')
	  ->that(function($suite) {
		  $suite['chernozem']['final'] = 'fantasy';
		  return $suite['chernozem']['final'];
	  })
	  ->equals('fantasy');

$suite->expects('has : ArrayAccess')
	  ->that(function($suite) {
		  $suite['chernozem']['final'] = 'fantasy';
		  return isset($suite['chernozem']['final']);
	  })
	  ->equals(true);

$suite->expects('remove : ArrayAccess')
	  ->that(function($suite) {
		  $suite['chernozem']['final'] = 'fantasy';
		  unset($suite['chernozem']['final']);
		  return isset($suite['chernozem']['final']);
	  })
	  ->equals(false);

$suite->hydrate(function($suite) {
	$suite['fruits'] = [
		'banana' => 'yellow',
		'strawberry' => 'red',
		'lemon' => 'green',
		'blood_orange' => 'red'
	];
	$suite['chernozem'] = new Chernozem\Container($suite['fruits']);
});

$suite->expects('constructor/toArray()')
	  ->that(function($suite) {
		  return $suite['chernozem']->toArray();
	  })
	  ->equals($suite['fruits']);

$suite->expects('clear()')
	  ->that(function($suite) {
		  $suite['chernozem']->clear();
		  return $suite['chernozem']->toArray();
	  })
	  ->equals([]);

$suite->expects('set/get : methods')
	  ->that(function($suite) {
		  return $suite['chernozem']->getBloodOrange();
	  })
	  ->equals('red');

########################################################### Loops

$suite = new MiniSuite\Suite('Loops');

$suite->hydrate(function($suite) {
	$suite['fruits'] = [
		'banana' => 'yellow',
		'strawberry' => 'red',
		'lemon' => 'green',
		'blood orange' => 'red'
	];
	$suite['chernozem'] = new Chernozem\Container($suite['fruits']);
});

$suite->expects('foreach()')
	  ->that(function($suite) {
		  $values = [];
		  foreach($suite['chernozem'] as $key => $value) {
			  $values[$key] = $value;
		  }
		  return $values;
	  })
	  ->equals($suite['fruits']);

$suite->expects('count()')
	  ->that(count($suite['chernozem']))
	  ->equals(4);

########################################################### Inflectors

$suite = new MiniSuite\Suite('Inflectors');

$suite->hydrate(function($suite) {
	$suite['value'] = $suite->service(function($suite) {
		return new Chernozem\Value('hello');
	});
	$suite['value']->addInputInflector(function($value) {
		if(strlen($value) > 8) {
			throw new Exception();
		}
		return $value;
	});
	$suite['value']->addOutputInflector(function($value) {
		return strtoupper($value);
	});
});

$suite->expects('getValue()/addOutputInflector()')
	  ->that($suite['value']->getValue())
	  ->equals('HELLO');

$suite->expects('getRawValue()')
	  ->that($suite['value']->getRawValue())
	  ->equals('hello');

$suite->expects('setValue()/addInputInflector() : invalid')
	  ->that($suite->protect(function($suite) {
		  $suite['value']->setValue('wonderful beach with great sunshine');
	  }))
	  ->throws();

$suite->expects('setValue()/addInputInflector() : valid')
	  ->that(function($suite) {
		  $suite['value']->setValue('bip');
		  return $suite['value']->getValue();
	  })
	  ->equals('BIP');

$suite->hydrate(function($suite) {
	$suite['fruits'] = [
		'banana' => 'yellow',
		'strawberry' => 'red',
		'lemon' => 'green',
		'blood orange' => 'red'
	];
	$suite['chernozem'] = new Chernozem\Container($suite['fruits']);
	$suite['chernozem']->setter('banana', function($value) {
		if(strlen($value) > 8) {
			throw new Exception();
		}
		return $value;
	});
	$suite['chernozem']->getter('banana', function($value) {
		return strtoupper($value);
	});
});

$suite->expects('setter()')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['banana'] = 'wonderful beach with great sunshine';
	  }))
	  ->throws();

$suite->expects('getter()')
	  ->that($suite['chernozem']['banana'])
	  ->equals('YELLOW');

########################################################### Factory closures

$suite = new MiniSuite\Suite('Factory closures');

class Factory {
	protected $a = 0;
	public function get() { return ++$this->a; }
}

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container();
	$suite['chernozem']['factory'] = $suite['chernozem']->factory(function($chernozem) use($suite) {
			$suite->expects('factory() : passed container')
				  ->that($chernozem)
				  ->isInstanceOf('Chernozem\Container');
			return new Factory();
	  });
});

$suite->expects('factory() : first get')
	  ->that($suite['chernozem']['factory']->get())
	  ->equals(1);

$suite->expects('factory() : second get')
	  ->that($suite['chernozem']['factory']->get())
	  ->equals(1);

$suite->hydrate(function(){});
$suite['chernozem'] = new Chernozem\Container();
$suite['chernozem']['service'] = $suite['chernozem']->service(function($chernozem) use($suite) {
	$suite->expects('service() : passed container')
		  ->that($chernozem)
		  ->isInstanceOf('Chernozem\Container');
	return new Factory();
});

$suite->expects('service() : first get')
	  ->that($suite['chernozem']['service']->get())
	  ->equals(1);

$suite->expects('service() : second get')
	  ->that($suite['chernozem']['service']->get())
	  ->equals(2);

########################################################### Type hinting

$suite = new MiniSuite\Suite('Type hinting');

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container(array(
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
	$suite['chernozem']->hint('int', 'int');
	$suite['chernozem']->hint('integer', 'integer');
	$suite['chernozem']->hint('float', 'float');
	$suite['chernozem']->hint('double', 'double');
	$suite['chernozem']->hint('bool', 'bool');
	$suite['chernozem']->hint('boolean', 'boolean');
	$suite['chernozem']->hint('string', 'string');
	$suite['chernozem']->hint('array', 'array');
	$suite['chernozem']->hint('class', 'Factory');
	$suite['chernozem']->hint('resource', 'resource');
});

$suite->expects('int : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['int'] = 1.72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('int : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['int'] = 72;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('integer : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['integer'] = 1.72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('integer : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['integer'] = 72;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('float : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['float'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('float : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['float'] = 1.72;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('double : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['float'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('double : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['float'] = 1.72;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('bool : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['bool'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('bool : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['bool'] = true;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('boolean : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['boolean'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('boolean : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['boolean'] = true;
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('string : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['string'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('string : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['string'] = 'string';
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('array : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['array'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('array : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['array'] = array();
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('class : fail')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['class'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

$suite->expects('class : pass')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['class'] = new Factory();
	  }))
	  ->doesNotThrow('Chernozem\ContainerException');

$suite->expects('unsupported type')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['resource'] = 72;
	  }))
	  ->throws('Chernozem\ContainerException');

########################################################### Read only values

$suite = new MiniSuite\Suite('Read only values');

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container(array('foo' => 'bar'));
});

$suite->expects('readonly()')
	  ->that($suite->protect(function($suite) {
		$suite['chernozem']->readonly('foo');
		$suite['chernozem']['foo'] = 'test';
	  }))
	  ->throws('Chernozem\ContainerException');

########################################################### Service providers

$suite = new MiniSuite\Suite('Service providers');

class MyService implements Chernozem\ServiceProviderInterface {
	public function register(Interop\Container\ContainerInterface $container) {
		$container['foo'] = 'bar';
	}
}

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container();
});

$suite->expects('register()')
	  ->that(function($suite) {
		$suite['chernozem']->register(new MyService);
		return $suite['chernozem']['foo'];
	  })
	  ->equals('bar');

########################################################### Delegate container

$suite = new MiniSuite\Suite('Delegate container');

$suite->hydrate(function($suite) {
	$suite['chernozem'] = new Chernozem\Container();
	$delegate = new Chernozem\Container();
	$delegate['foo'] = 'bar';
	$suite['chernozem']->delegate($delegate);
});


$suite->expects('factory() : does not throw')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['factory'] = $suite['chernozem']->factory(function($container) use($suite) {
			  $suite->expects('factory() : value')
				    ->that($container['foo'])
				    ->equals('bar');
		  });
		  return $suite['chernozem']['factory'];
	  }))
	  ->doesNotThrow();

$suite->expects('service() : does not throw')
	  ->that($suite->protect(function($suite) {
		  $suite['chernozem']['service'] = $suite['chernozem']->service(function($container) use($suite) {
			  $suite->expects('service() : value')
				    ->that($container['foo'])
				    ->equals('bar');
		  });
		  return $suite['chernozem']['service'];
	  }))
	  ->doesNotThrow();

########################################################### Composite container

$suite = new MiniSuite\Suite('Composite container');

$suite->hydrate(function($suite) {
	$c1 = new Chernozem\Container();
	$c1['bar'] = 'foo';
	$c2 = new Chernozem\Container();
	$c2['foo'] = 'bar';
	$suite['composite'] = new Chernozem\Composite(array($c1));
	$suite['composite']->add($c2);
});

$suite->expects('has() : first container')
	  ->that($suite['composite']->has('bar'))
	  ->equals(true);

$suite->expects('has() : second container')
	  ->that($suite['composite']->has('foo'))
	  ->equals(true);

$suite->expects('has() : not found')
	  ->that($suite['composite']->has('pwet'))
	  ->equals(false);

$suite->expects('get() : first container')
	  ->that($suite['composite']->get('bar'))
	  ->equals('foo');

$suite->expects('get() : second container')
	  ->that($suite['composite']->get('foo'))
	  ->equals('bar');