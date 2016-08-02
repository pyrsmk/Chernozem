<?php

namespace Chernozem;

use Closure;
use ArrayAccess;
use Iterator;
use Countable;
use Interop\Container\ContainerInterface;

/*
	Basic container
*/
class Container implements ContainerInterface, ArrayAccess, Iterator, Countable {
	
	/*
		array $values
		Interop\Container\ContainerInterface $delegate_container
	*/
	protected $values = [];
	protected $delegate_container;
	
	/*
		Constructor
		
		Parameters
			array $values
	*/
	public function __construct(array $values = []) {
		// Append the specified values
		foreach($values as $id => $value) {
			$this->set($id, $value);
		}
		// Init delegate container
		$this->delegate_container = $this;
	}
	
	/*
		Set the delegate container
	*/
	public function delegate(ContainerInterface $container) {
		$this->delegate_container = $container;
	}
	
	/*
		Register a service provider
		
		Parameters
			Chernozem\ServiceProviderInterface $provider
	*/
	public function register(ServiceProviderInterface $provider) {
		$provider->register($this->delegate_container);
	}
	
	/*
		Verify if a key exists in the container
		
		Parameters
			mixed $id
		
		Return
			boolean
	*/
	public function has($id) {
		return array_key_exists($this->_format($id), $this->values);
	}
	
	/*
		Set a value
		
		Parameters
			mixed $id
			mixed $value
	*/
	public function set($id, $value) {
		// Format id
		$id = $this->_format($id);
		// Instantiate value
		if(!($value instanceof Value)) {
			$value = new Value($value);
		}
		// Set value
		if(isset($id)) {
			if(!isset($this->values[$id])) {
				$this->values[$id] = $value;
			}
			else {
				$this->values[$id]->setValue($value);
			}
		}
		else {
			$this->values[] = $value;
		}
	}
	
	/*
		Get a value from the container
		
		Parameters
			mixed $id
		
		Return
			mixed
	*/
	public function get($id) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Get value
		return $this->values[$id]->getValue();
	}
	
	/*
		Remove a value
		
		Parameters
			mixed $id
	*/
	public function remove($id) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Remove value
		unset($this->values[$id]);
	}
	
	/*
		Clear all values
	*/
	public function clear() {
		$this->values = [];
	}
	
	/*
		Get raw value
		
		Parameters
			mixed $id
		
		Return
			mixed
	*/
	public function raw($id) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Remove value
		return $this->values[$id]->getRawValue();
	}
	
	/*
		Add a setter inflector to the specified value
		
		Parameters
			mixed $id
			Closure $setter
	*/
	public function setter($id, Closure $setter) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Add inflector
		$this->values[$id]->addInputInflector($setter);
	}
	
	/*
		Add a getter inflector to the specified value
		
		Parameters
			mixed $id
			Closure $getter
	*/
	public function getter($id, Closure $getter) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Add inflector
		$this->values[$id]->addOutputInflector($getter);
	}
	
	/*
		Make a closure a service which always return the same instance
		
		Parameters
			Closure $value
		
		Return
			Chernozem\Value
	*/
	public function service(Closure $value) {
		// Instantiate value
		$value = new Value($value);
		// Set inflector
		$instance = null;
		$value->addOutputInflector(function($value) use(&$instance) {
			if($instance === null) {
				$instance = $value($this->delegate_container);
			}
			return $instance;
		});
		return $value;
	}
	
	/*
		Make a closure a service which always return a new instance
		
		Parameters
			Closure $value
		
		Return
			Chernozem\Value
	*/
	public function factory(Closure $value) {
		// Instantiate value
		$value = new Value($value);
		// Set inflector
		$value->addOutputInflector(function($value) {
			return $value($this->delegate_container);
		});
		return $value;
	}
	
	/*
		Set a value as readonly
		
		Parameters
			mixed $id
	*/
	public function readonly($id) {
		// Format id
		$id = $this->_format($id);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Set inflector
		$this->values[$id]->addInputInflector(function($value) use($id) {
			throw new ContainerException("Cannot overwrite '$id' value, set in read only mode");
		});
	}
	
	/*
		Set type hinting
		
		Parameters
			mixed $id
			string $type
	*/
	public function hint($id, $type) {
		// Format
		$id = $this->_format($id);
		$type = strtolower($type);
		// Verify existence
		if(!$this->has($id)) {
			throw new NotFoundException("'$id' value does not exist");
		}
		// Set inflector
		$this->values[$id]->addInputInflector(function($value) use($id, $type) {
			$types = ['boolean', 'integer', 'double', 'string', 'array'];
			if($type == 'int') $type = 'integer';
			if($type == 'float') $type = 'double';
			if($type == 'bool') $type = 'boolean';
			if(in_array($type, $types)) {
				$t = gettype($value);
				if($t != $type) {
					throw new ContainerException("Cannot set '$id' value, '$type' expected, '$t' provided");
				}
			}
			else if(gettype($value) == 'object') {
				if(!($value instanceof $type)) {
					$c = get_class($value);
					throw new ContainerException("Cannot set '$id' value, '$type' class expected, '$c' provided");
				}
			}
			else {
				throw new ContainerException("'$id' value should be an object or the provided '$type' type hint is not supported");
			}
			return $value;
		});
	}
	
	/*
		Format an id
		
		Parameters
			mixed $id
	*/
	protected function _format($id) {
		if(is_object($id)) {
			return spl_object_hash($id);
		}
		else {
			return $id;
		}
	}

	/*
		Return all values

		Return
			array
	*/
	public function toArray() {
		$values = [];
		foreach($this->values as $id => $value) {
			$values[$id] = $value->getRawValue();
		}
		return $values;
	}

	/*
		Verify if the key exists

		Parameters
			mixed $id

		Return
			boolean
	*/
	public function offsetExists($id) {
		return $this->has($id);
	}

	/*
		Set a value

		Parameters
			mixed $id
			mixed $value
	*/
	public function offsetSet($id, $value) {
		$this->set($id, $value);
	}

	/*
		Return a value

		Parameters
			mixed $id

		Return
			mixed
	*/
	public function offsetGet($id) {
		return $this->get($id);
	}

	/*
		Unset a value

		Parameters
			mixed $id
	*/
	public function offsetUnset($id) {
		$this->remove($id);
	}

	/*
		Return the current value of the container

		Return
			mixed
	*/
	public function current() {
		return current($this->values)->getValue();
	}

	/*
		Return the current key of the container

		Return
			string
	*/
	public function key() {
		return key($this->values);
	}

	/*
		Advance the internal pointer of the container
	*/
	public function next() {
		next($this->values);
	}

	/*
		Reset the internal pointer of the container
	*/
	public function rewind() {
		reset($this->values);
	}

	/*
		Verify if the current value is valid

		Return
			boolean
	*/
	public function valid() {
		return key($this->values) !== null;
	}

	/*
		Return the number of values in the container

		Return
			integer
	*/
	public function count() {
		return count($this->values);
	}

	/*
		Set/get magic method

		Parameters
			string $name
			array $arguments
	*/
	public function __call($name, $arguments) {
		// Format CamelCase property
		$format = function($input) {
			preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
			$ret = $matches[0];
			foreach($ret as &$match) {
				$match = ($match == strtoupper($match) ? strtolower($match) : lcfirst($match));
			}
			return implode('_', $ret);
		};
		// Extract params
		$method = substr($name, 0, 3);
		$property = $format(substr($name, 3));
		// Verify
		if(!$this->has($property)) {
			throw new NotFoundException("'$property' value does not exist");
		}
		// Call
		switch($method) {
			case 'set':
			case 'get':
				array_unshift($arguments, $property);
				return call_user_func_array([$this, $method], $arguments);
				break;
			default:
				throw new ContainerException("'$method' method not supported");
		}
	}
	
}