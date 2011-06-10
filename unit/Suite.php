<?php

namespace Lumy\Unit;

/*
    An easy unit testing suite
    
    Author: Aurélien Delogu <dev@dreamysource.fr>
*/
class Suite{
    
    /*
        string $_name               : the suite name
        Closure $_before            : the closure to run before each test
        Closure $_after             : the closure to run after each test
        array $_tests               : the test closure stack
        int $_test_expectations     : the current expected number of checks for a test
        string $_test_description   : the current test description
    */
    protected $_name;
    protected $_before;
    protected $_after;
    protected $_tests=array();
    protected $_test_expectations;
    protected $_test_description;
    
    /*
        Constructor
        
        string $name: the suite name
    */
    public function __construct($name){
        $this->_name=(string)$name;
    }
    
    /*
        Sets the closure that will be runned before each test
        
        Closure $closure    : the closure to run
        
        Returns             : Lumy\Unit\Suite
    */
    public function before(\Closure $closure){
        $this->_before=$closure;
        return $this;
    }
    
    /*
        Sets the closure that will be runned after each test
        
        Closure $closure    : the closure to run
        
        Returns             : Lumy\Unit\Suite
    */
    public function after(\Closure $closure){
        $this->_after=$closure;
        return $this;
    }
    
    /*
        Adds a test
        
        string $description : the test description
        int $expectations   : the number of checks expected
        Closure $closure    : the closure to run
        
        Returns             : Lumy\Unit\Suite
    */
    public function test($description,$expectations,\Closure $closure){
        $this->_tests[]=array(
            'description'   => (string)$description,
            'expectations'  => (int)$expectations,
            'closure'       => $closure
        );
        return $this;
    }
    
    /*
        Clears the test closure stack
        
        Returns: Lumy\Unit\Suite
    */
    public function clear(){
        $this->_tests=array();
        return $this;
    }
    
    /*
        Checks if the result is true
        
        string $description : the description of the specific verification
        boolean $result     : the result
        
        Returns             : Lumy\Unit\Suite
    */
    public function check($description,$result){
        ++$this->_test_expectations;
        if((bool)$result){
            $this->_checkPassed($this->_test_description,$description);
        }
        else{
            $this->_checkFailed($this->_test_description,$description);
        }
        return $this;
    }
    
    /*
        Runs the suite
        
        Returns: Lumy\Unit\Suite
    */
    public function run(){
        $this->_beforeSuite();
        // Browse tests
        foreach($this->_tests as $test){
            // Before
            $this->_beforeTest($test['description'],$test['expectations']);
            if($before=$this->_before){
                $before();
            }
            // Init the test
            $this->_test_description=$test['description'];
            $this->_test_expectations=0;
            // Run the test
            $closure=$test['closure'];
            $closure();
            // After
            if($after=$this->_after){
                $after();
            }
            $this->_afterTest($test['description'],$test['expectations'],$this->_test_expectations);
        }
        $this->_afterSuite();
        return $this;
    }
    
    /*
        Triggered when a check has passed
        
        string $test_description    : the test description
        string $check_description   : the check description
    */
    protected function _checkPassed($test_description,$check_description){}
    
    /*
        Triggered when a check has failed
        
        string $test_description    : the test description
        string $check_description   : the check description
    */
    protected function _checkFailed($test_description,$check_description){
        throw new Suite\Failed("[$this->_name] $test_description: $check_description");
    }
    
    /*
        Triggered before running the suite
    */
    protected function _beforeSuite(){}
    
    /*
        Triggered after running the suite
    */
    protected function _afterSuite(){}
    
    /*
        Triggered before running a test
        
        string $description : the test description
        int $expected       : the expected number of checks to run
    */
    protected function _beforeTest($description,$expected){}
    
    /*
        Triggered after running a test
        
        string $description: the test description
        int $expected       : the expected number of checks to run
        int $runned         : the runned number of checks
    */
    protected function _afterTest($description,$expected,$runned){
        if($expected!=$runned){
            throw new Suite\Failed("[$this->_name] $description: $runned/$expected checks runned");
        }
    }
    
}
