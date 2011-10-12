<?php

namespace Lumy\Suite;

/*
    Unit testing suite for HTTP environment

    Author
        Aurélien Delogu (dev@dreamysource.fr)

*/
class Http extends \Lumy\Suite{
    
    /*
        string $_title_markup   : the title markup, 'h1' by default
        string $_passed_class   : the passed item class, 'passed' by default
        string $_failed_class   : the failed item class, 'failed' by default
    */
    protected $_title_markup    = 'h1';
    protected $_passed_class    = 'passed';
    protected $_failed_class    = 'failed';
    
    /*
        Triggered before running the suite
    */
    protected function _beforeSuite(){
        echo "<$this->_title_markup>".get_class($this)."</$this->_title_markup>";
    }

    /*
        Triggered after running the suite
    */
    protected function _afterSuite(){}

    /*
        Display the test name
        
        Parameters
            string $name: the test name
    */
    protected function _displayTestName($name){
        echo "<span>$name</span><ul>";
    }
    
    /*
        Display the runned expectations for a test
        
        Parameters
            int $runned     : the runned number of checks
            int $expected   : the expected number of checks to run
    */
    protected function _displayTestExpectations($runned,$expected){
        if($runned!=$expected){
            echo '<li class="'.$this->_failed_class.'">'.$runned.'/'.$expected.' checks runned</li>';
        }
        echo '</ul>';
    }

    /*
        Triggered when a check has passed
        
        Parameters
            string $description: the check description
    */
    protected function _checkPassed($description){
        echo '<li class="'.$this->_passed_class.'">'.$description.'</li>';
    }
    
    /*
        Triggered when a check has failed
        
        Parameters
            string $description: the check description
    */
    protected function _checkFailed($description){
        echo '<li class="'.$this->_failed_class.'">'.$description.'</li>';
    }

    /*
        Set the title markup
        
        Parameters
            string $markup          : the markup
        
        Return
            Lumy\Suite\Http

        Throw
            Lumy\Suite\Exception    : if the markup variable is empty
    */
    public function setTitleMarkup($markup){
        if(!$markup){
            throw new Exception("The title markup can't be empty");
        }
        $this->_title_markup=(string)$markup;
        return $this;
    }
    
    /*
        Return the title markup
        
        Return
            string
    */
    public function getTitleMarkup(){
        return $this->_title_markup;
    }
    
    /*
        Set the passed item class
        
        Parameters
            string $class           : the class
        
        Return
            Lumy\Suite\Http

        Throw
            Lumy\Suite\Exception    : if the class is empty
    */
    public function setPassedClass($class){
        if(!$class){
            throw new Exception("The passed item class can't be empty");
        }
        $this->_passed_class=(string)$class;
        return $this;
    }
    
    /*
        Return the passed item class
        
        Return
            string
    */
    public function getPassedClass(){
        return $this->_passed_class;
    }
    
    /*
        Sets the failed item class
        
        string $class               : the class
        
        Returns                     : Lumy\Test\Suite\Http
        Throws Lumy\Suite\Exception : if the class is empty
    */
    public function setFailedClass($class){
        if(!$class){
            throw new Exception("The failed item class can't be empty");
        }
        $this->_failed_class=(string)$class;
        return $this;
    }
    
    /*
        Returns the failed item class
        
        Returns
            string
    */
    public function getFailedClass(){
        return $this->_failed_class;
    }
    
}
