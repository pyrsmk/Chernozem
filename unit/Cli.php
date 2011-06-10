<?php

namespace Lumy\Unit\Suite;

/*
    Unit testing suite for CLI
    
    Author: Aurélien Delogu <dev@dreamysource.fr>
*/
class Cli extends \Lumy\Unit\Suite{
    
    /*
        Triggered when a check has passed
        
        string $test_description    : the test description
        string $check_description   : the check description
    */
    protected function _checkPassed($test_description,$check_description){
        echo "      \033[0;32m$check_description\n";
    }
    
    /*
        Triggered when a check has failed
        
        string $test_description    : the test description
        string $check_description   : the check description
    */
    protected function _checkFailed($test_description,$check_description){
        echo "      \033[0;31m$check_description\n";
    }
    
    /*
        Triggered before running the suite
    */
    protected function _beforeSuite(){
        echo "\n  \033[1;33m$this->_name\n\n";
    }
    
    /*
        Triggered before running a test
        
        string $description : the test description
        int $expected       : the expected number of checks to run
    */
    protected function _beforeTest($description,$expected){
        if($description){
            echo "    \033[1;35m$description\n";
        }
    }
    
    /*
        Triggered after running a test
        
        string $description: the test description
        int $expected       : the expected number of checks to run
        int $runned         : the runned number of checks
    */
    protected function _afterTest($description,$expected,$runned){
        if($expected!=$runned){
            echo "      \033[0;31m$runned/$expected checks runned\n";
        }
        echo "\n";
    }
    
}
