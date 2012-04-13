<?php

namespace Lumy\Suite;

/*
     Unit testing suite for CLI environment

    Author
        Aurélien Delogu (dev@dreamysource.fr)
*/
class Cli extends \Lumy\Suite{

    
    /*
        Triggered before running the suite
    */
    protected function _beforeSuite(){
        echo "\n  \033[1;33m".$this->_name."\n\n";
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
        if($name){
            echo "    \033[1;35m$name\n";
        }
    }
    
    /*
        Display the runned expectations for a test
        
        Parameters
            int $runned     : the runned number of checks
            int $expected   : the expected number of checks to run
    */
    protected function _displayTestExpectations($runned,$expected){
        if($runned!=$expected){
            echo "      \033[0;31m$runned/$expected checks runned\n";
        }
        echo "\n";
    }

    /*
        Triggered when a check has passed
        
        Parameters
            string $description: the check description
    */
    protected function _checkPassed($description){
        echo "      \033[0;32m$description\n";
    }
    
    /*
        Triggered when a check has failed
        
        Parameters
            string $description: the check description
    */
    protected function _checkFailed($description){
        echo "      \033[0;31m$description\n";
    }

}
