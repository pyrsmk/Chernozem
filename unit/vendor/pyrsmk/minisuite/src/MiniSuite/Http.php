<?php

namespace MiniSuite;

use MiniSuite;

/*
	HTTP environment

	Author
		Aurélien Delogu (dev@dreamysource.fr)

*/
class Http extends MiniSuite{

	/*
		Triggered before running tests
	*/
	protected function _beforeTests(){
		echo "<h1>".$this->name."</h1><ul>";
	}

	/*
		Triggered after running tests
	*/
	protected function _afterTests(){
		echo '</ul>';
	}

	/*
		Triggered when a test has passed
		
		Parameters
			string $message
	*/
	protected function _testPassed($message){
		echo '<li style="color:green;">'.$message.'</li>';
	}

	/*
		Triggered when a test has failed
		
		Parameters
			string $message
	*/
	protected function _testFailed($message){
		echo '<li style="color:red;">'.$message.'</li>';
	}

}
