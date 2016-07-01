<?php

namespace Chernozem;

use Closure;
use Interop\Container\ContainerInterface;

/*
	Composite container
*/
class Composite extends Container {
	
	/*
		array $values
	*/
	protected $values;
	
	/*
		Constructor
		
		Parameters
			array $values
	*/
	public function __construct(array $values = array()) {
		foreach($values as $container) {
			if(!($container instanceof ContainerInterface)) {
				throw new ContainerException("A provided container does not implement ContainerInterface");
			}
			$this->add($container);
		}
		parent::__construct();
	}
	
	/*
		Add a container to the stack
		
		Parameters
			Interop\Container\ContainerInterface $container
	*/
	public function add(ContainerInterface $container) {
		$this->values[] = $container;
	}
	
	/*
		Verify if a key exists in the container
		
		Parameters
			mixed $id
		
		Return
			boolean
	*/
	public function has($id) {
		foreach($this->values as $container) {
			if($container->has($id)) {
				return true;
			}
		}
		return false;
	}
	
	/*
		Set a value
		
		Parameters
			mixed $id
			mixed $value
	*/
	public function set($id, $value) {
		throw new ContainerException("set() method disabled");
	}
	
	/*
		Get a value from the container
		
		Parameters
			mixed $id
		
		Return
			mixed
	*/
	public function get($id) {
		foreach($this->values as $container) {
			if($container->has($id)) {
				return $container->get($id);
			}
		}
		throw new NotFoundException("'$id' value not found");
	}
	
	/*
		Remove a value
		
		Parameters
			mixed $id
	*/
	public function remove($id) {
		throw new ContainerException("remove() method disabled");
	}
	
	/*
		Get raw value
		
		Parameters
			mixed $id
		
		Return
			mixed
	*/
	public function raw($id) {
		throw new ContainerException("raw() method disabled");
	}
	
	/*
		Add a setter inflector to the specified value
		
		Parameters
			mixed $id
			Closure $setter
	*/
	public function setter($id, Closure $setter) {
		throw new ContainerException("setter() method disabled");
	}
	
	/*
		Add a getter inflector to the specified value
		
		Parameters
			mixed $id
			Closure $getter
	*/
	public function getter($id, Closure $getter) {
		throw new ContainerException("getter() method disabled");
	}
	
	/*
		Make a closure a service which always return the same instance
		
		Parameters
			Closure $value
		
		Return
			Chernozem\Value
	*/
	public function service(Closure $value) {
		throw new ContainerException("service() method disabled");
	}
	
	/*
		Make a closure a service which always return a new instance
		
		Parameters
			Closure $value
		
		Return
			Chernozem\Value
	*/
	public function factory(Closure $value) {
		throw new ContainerException("factory() method disabled");
	}
	
	/*
		Set a value as readonly
		
		Parameters
			mixed $id
	*/
	public function readonly($id) {
		throw new ContainerException("readonly() method disabled");
	}
	
	/*
		Set type hinting
		
		Parameters
			mixed $id
			string $type
	*/
	public function hint($id, $type) {
		throw new ContainerException("hint() method disabled");
	}
	
}