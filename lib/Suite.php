<?php

namespace Lumy;

/*
    An ease of use unit testing suite

    Version : 0.3.2
    Author  : Aurélien Delogu (dev@dreamysource.fr)
    Site    : https://github.com/pyrsmk/Lumy
    License : MIT
*/
abstract class Suite{
    
    /*
        string $_name   : the suite name
        int $_runned    : the number of runned checks in one test
    */
    protected $_name;
    protected $_runned;
    
    /*
        Run the suite
        
        Return
            Lumy\Suite
    */
    public function run(){
        // Get suite name
        $name=get_class($this);
        if($pos=strrpos($name,'\\')){
            ++$pos;
        }
        $this->_name=substr(str_replace('_',' ',$name),$pos);
        // Run before routines
        $this->_beforeSuite();
        // Pre-browse
        $methods=array();
        foreach(get_class_methods($this) as $method){
            if(preg_match('#^test([a-zA-Z_]+)(\d*)$#',$method,$matches)){
                $value=array($matches[0],str_replace('_',' ',$matches[1]));
                if(($index=$matches[2])!=''){
                    $methods[$index]=$value;
                }
                else{
                    $methods[]=$value;
                }
            }
        }
        ksort($methods);
        // Browse tests
        foreach($methods as $method){
            list($method,$name)=$method;
            // Init the test
            $this->_runned=0;
            $this->_displayTestName($name);
            // Run the test
            $expected=$this->$method();
            // Close the test
            $this->_displayTestExpectations($this->_runned,(int)$expected);
        }
        // Run after routines
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
            if(!$description){
                $description='Passed';
            }
            $this->_checkPassed($description);
        }
        else{
            if(!$description){
                $description='Failed';
            }
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
