<?php

/*
	A mini unit testing tool

	Author
		Aurélien Delogu (dev@dreamysource.fr)
*/
abstract class MiniSuite{

	/*
		string $name
		array $tests
	*/
	protected $name;
	protected $tests=array();

	/*
		Constructor

		Parameters
			string $name
	*/
	public function __construct($name='Testing suite'){
		$this->name=(string)$name;
	}

	/*
		Add one test to the stack

		Parameters
			string $message
			callable $callable

		Return
			MiniSuite
	*/
	public function test($message,$callable){
		if(!is_callable($callable)){
			throw new Exception("Second parameter of test() method must be a callable");
		}
		$this->tests[]=array(
			'message' => (string)$message,
			'callable'=> $callable
		);
	}
	
	/*
		Run the tests
		
		Return
			MiniSuite
	*/
	public function run(){
		$this->_beforeTests();
		foreach($this->tests as $test){
			try{
				if($test['callable']()){
					$this->_testPassed($test['message']);
				}
				else{
					$this->_testFailed($test['message']);
				}
			}
			catch(\Exception $e){
				$this->_testFailed($test['message']);
			}
		}
		$this->_afterTests();
		return $this;
	}

	/*
		Triggered before running tests
	*/
	abstract protected function _beforeTests();

	/*
		Triggered after running tests
	*/
	abstract protected function _afterTests();

	/*
		Triggered when a test has passed
		
		Parameters
			string $message
	*/
	abstract protected function _testPassed($message);
	
	/*
		Triggered when a test has failed
		
		Parameters
			string $message
	*/
	abstract protected function _testFailed($message);
	
}
