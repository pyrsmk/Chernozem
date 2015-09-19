<?php

/*
	An advanced dependency injection container

	Author
		Aurélien Delogu (dev@dreamysource.fr)
*/
class Chernozem implements ArrayAccess, Iterator, Countable {

	/*
		array $__chernozem_values			: container's values
		array $__chernozem_services			: registered service list
		array $__chernozem_service_values	: service value list
	*/
	protected $__chernozem_values		= array();
	private $__chernozem_services		= array();
	private $__chernozem_service_values	= array();

	/*
		Constructor

		Parameters
			array, object $values: a value list to fill in the container
	*/
	public function __construct($values = array()) {
		if(is_array($values) || ($values instanceof Traversable)) {
			foreach($values as $name => $value) {
				$this->offsetSet($name, $value);
			}
		}
	}

	/*
		Return values from the container

		Return
			array
	*/
	public function toArray() {
		return $this->__chernozem_values;
	}

	/*
		Make a closure a service

		Parameters
			string, int, object $key

		Return
			Chernozem
	*/
	final public function service($key) {
		if(!($this->offsetGet($key) instanceof Closure)) {
			throw new Exception("'$key' value must a closure to be able to set it as a service");
		}
		$this->__chernozem_services[$this->__chernozemFormatKey($key)] = 1;
		return $this;
	}

	/*
		Make a service a simple closure

		Parameters
			string, integer, object $key

		Return
			Chernozem
	*/
	final public function unservice($key) {
		$key = $this->__chernozemFormatKey($key);
		if(array_key_exists($key, $this->__chernozem_service_values)) {
			unset($this->__chernozem_service_values[$key]);
			unset($this->__chernozem_services[$key]);
		}
		return $this;
	}

	/*
		Verify if the key exists

		Parameters
			string, integer, object $key

		Return
			boolean
	*/
	public function offsetExists($key) {
		$key = $this->__chernozemFormatKey($key);
		return property_exists($this, $key) ||
			   property_exists($this, '_'.$key) ||
			   array_key_exists($key, $this->__chernozem_values);
	}

	/*
		Set a value

		Parameters
			mixed $key
			mixed $value
	*/
	public function offsetSet($key,$value){
		// Format key
		$key = $this->__chernozemFormatKey($key);
		// Property exists
		if(property_exists($this, $key)) {
			$this->$key = $value;
		}
		// Property locked
		else if(property_exists($this, '_'.$key)) {
			throw new Exception("'$key' value is locked");
		}
		// Add to container
		else{
			if($key) {
				$this->__chernozem_values[$key] = $value;
			}
			else{
				$this->__chernozem_values[] = $value;
			}
		}
		// Remove service
		$this->unservice($key);
	}

	/*
		Return a value

		Parameters
			string, integer, object $key

		Return
			mixed
	*/
	public function offsetGet($key) {
		// Format key
		$key = $this->__chernozemFormatKey($key);
		// Property exists
		if(property_exists($this, $key)) {
			return $this->__chernozemService($key, $this->$key);
		}
		// Locked property
		elseif(property_exists($this, '_'.$key)) {
			$_key = '_'.$key;
			return $this->$_key;
		}
		// Container
		else if(isset($this->__chernozem_values[$key])) {
			return $this->__chernozemService($key, $this->__chernozem_values[$key]);
		}
		else{
			return null;
		}
	}

	/*
		Unset a value

		Parameters
			string, integer, object $key
	*/
	public function offsetUnset($key) {
		$key = $this->__chernozemFormatKey($key);
		if(isset($this->__chernozem_values[$key])) {
			unset($this->__chernozem_values[$key]);
		}
	}

	/*
		Return the current value of the container

		Return
			mixed
	*/
	public function current() {
		return current($this->__chernozem_values);
	}

	/*
		Return the current key of the container

		Return
			string
	*/
	public function key() {
		return key($this->__chernozem_values);
	}

	/*
		Advance the internal pointer of the container
	*/
	public function next() {
		next($this->__chernozem_values);
	}

	/*
		Reset the internal pointer of the container
	*/
	public function rewind() {
		reset($this->__chernozem_values);
	}

	/*
		Verify if the current value is valid

		Return
			boolean
	*/
	public function valid() {
		return key($this->__chernozem_values) !== null;
	}

	/*
		Return the number of values in the container

		Return
			integer
	*/
	public function count() {
		return count($this->__chernozem_values);
	}

	/*
		Launch closure if it's set as a service

		Parameters
			integer, string $key
			mixed $value

		Return
			mixed $value
	*/
	final private function __chernozemService($key, $value) {
		if(isset($this->__chernozem_services[$key])) {
			$service_value = &$this->__chernozem_service_values[$key];
			if(is_null($service_value)) {
				$service_value = $value();
			}
			$value = $service_value;
		}
		return $value;
	}

	/*
		Format a key

		Parameters
			mixed $key

		Return
			mixed
	*/
	final private function __chernozemFormatKey($key) {
		if(is_object($key)) {
			return spl_object_hash($key);
		}
		else{
			return $key;
		}
	}

}
