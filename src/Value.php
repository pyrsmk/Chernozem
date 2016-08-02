<?php

namespace Chernozem;

use Closure;

/*
	A container value
*/
class Value {
	
	/*
		mixed $value
		array $inputs
		array $outputs
	*/
	private $value;
	private $inputs = [];
	private $outputs = [];
	
	/*
		Constructor
		
		Parameters
			mixed $value
	*/
	public function __construct($value) {
		$this->setValue($value);
	}
	
	/*
		Set value
		
		Parameters
			mixed $value
	*/
	public function setValue($value) {
		if($value instanceof Value) {
			$value = $value->getRawValue();
		}
		foreach($this->inputs as $inflector) {
			$value = $inflector($value);
		}
		$this->value = $value;
	}
	
	/*
		Get value
		
		Return
			mixed
	*/
	public function getValue() {
		$value = $this->value;
		foreach($this->outputs as $inflector) {
			$value = $inflector($value);
		}
		return $value;
	}
	
	/*
		Get raw value
		
		Return
			mixed
	*/
	public function getRawValue() {
		return $this->value;
	}
	
	/*
		Add input inflector
		
		Parameters
			Closure $inflector
	*/
	public function addInputInflector(Closure $inflector) {
		$this->inputs[] = $inflector;
	}
	
	/*
		Add output inflector
		
		Parameters
			Closure $inflector
	*/
	public function addOutputInflector(Closure $inflector) {
		$this->outputs[] = $inflector;
	}
	
}