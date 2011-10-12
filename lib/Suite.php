<?php

namespace Lumy;

/*
    An ease of use unit testing suite

    Author
        Aurélien Delogu (dev@dreamysource.fr)
*/
abstract class Suite{
    
    /*
        int $_runned: the number of runned checks in one test
    */
    protected $_runned;
    
    /*
        Run the suite
        
        Return
            Lumy\Suite
    */
    public function run(){
        $this->_beforeSuite();
        // Browse tests
        foreach(get_class_methods($this) as $method){
            // Verify the method name
            if(strpos($method,'test')!==0){
                continue;
            }
            // Init the test
            $this->_runned=0;
            $this->_displayTestName((string)substr($method,4));
            // Run the test
            $expected=$this->$method();
            // Close the test
            $this->_displayTestExpectations($this->_runned,(int)$expected);
        }
        $this->_afterSuite();
        return $this;
    }

    /*
        Check if the result is true
        
        Parameters
            string $description : the description of the specific verification
            boolean $result     : the result
        
        Return
            Lumy\Suite
    */
    public function check($description,$result){
        ++$this->_runned;
        if((bool)$result){
            $this->_checkPassed($description);
        }
        else{
            $this->_checkFailed($description);
        }
        return $this;
    }
    
    /*
        Triggered before running the suite
    */
    abstract protected function _beforeSuite();
    
    /*
        Triggered after running the suite
    */
    abstract protected function _afterSuite();

    /*
        Display the test name
        
        Parameters
            string $name: the test name
    */
    abstract protected function _displayTestName($name);
    
    /*
        Display the runned expectations for a test
        
        Parameters
            int $runned     : the runned number of checks
            int $expected   : the expected number of checks to run
    */
    abstract protected function _displayTestExpectations($runned,$expected);

    /*
        Triggered when a check has passed
        
        Parameters
            string $description: the check description
    */
    abstract protected function _checkPassed($description);
    
    /*
        Triggered when a check has failed
        
        Parameters
            string $description: the check description
    */
    abstract protected function _checkFailed($description);
    
}
