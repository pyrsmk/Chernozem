<?php

namespace MiniSuite;

use MiniSuite;

/*
	 CLI environment

	Author
		Aurélien Delogu (dev@dreamysource.fr)
*/
class Cli extends MiniSuite{

	/*
		boolean $colors
	*/
	protected $colors=true;

	/*
		Disable ANSI colors (some environments don't support them, like Windows)

		Return
			MiniSuite\Cli
	*/
	public function disableAnsiColors(){
		$this->colors=false;
		return $this;
	}

	/*
		Triggered before running tests
	*/
	protected function _beforeTests(){
		if($this->colors){
			echo "\n  \033[1;33m$this->name\n\n";
		}
		else{
			echo "\n  $this->name\n\n";
		}
	}

	/*
		Triggered after running tests
	*/
	protected function _afterTests(){
		echo "\n";
	}

	/*
		Triggered when a test has passed
		
		Parameters
			string $message
	*/
	protected function _testPassed($message){
		if($this->colors){
			echo "      \033[0;32m$message\n";
		}
		else{
			echo "      Passed : $message\n";
		}
	}

	/*
		Triggered when a test has failed
		
		Parameters
			string $message
	*/
	protected function _testFailed($message){
		if($this->colors){
			echo "      \033[0;31m$message\n";
		}
		else{
			echo "      Failed : $message\n";
		}
	}

}
