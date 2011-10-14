<?php

namespace Chernozem;

/*
    Properties-oriented dependency injection manager
    
    Version : 1.0.2
    Author  : Aurélien Delogu (dev@dreamysource.fr)
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
abstract class Properties implements \ArrayAccess{
    
    /*
        Constructor
        
        Parameters
            array $values: a value list to fill in the container
    */
    public function __construct(array $values=array()){
        foreach($values as $name=>$value){
            $this->offsetSet($name,$value);
        }
    }
    
    /*
        Verify if the option exists
        
        Parameters
            string $name: the option name
        
        Return
            boolean
    */
    public function offsetExists($name){
        $name=(string)$name;
        return property_exists($this,$name) || property_exists($this,'_'.$name);
    }
    
    /*
        Set a value
        
        Parameters
            string $name    : the option name
            mixed $value    : the value
        
        Throw
            Exception       : if the option does not exist
            Exception       : if the option is locked
            Exception       : if the provided option name's type is invalid
    */
    public function offsetSet($name,$value){
        // Format
        $name=(string)$name;
        // Verify if locked
        if(property_exists($this,'_'.$name)){
            throw new \Exception("'$name' option is locked");
        }
        else{
            // Verify option existence
            if(!property_exists($this,$name)){
                throw new \Exception("'$name' option does not exist");
            }
        }
        // Verify option type
        if(($type1=gettype($this->$name))!=($type2=gettype($value)) && $type1!='NULL'){
            throw new \Exception("Bad '$name' option's value type, $type1 expected but $type2 provided");
        }
        // Register the value
        $this->$name=$value;
    }
    
    /*
        Return a value
        
        Parameters
            string $name    : the option name
        
        Return
            mixed
        
        Throw
            Exception       : if the option does not exist
    */
    public function offsetGet($name){
        // Format
        $name=(string)$name;
        // Verify if locked
        if(property_exists($this,'_'.$name)){
            $name='_'.$name;
        }
        else{
            // Verify option existence
            if(!property_exists($this,$name)){
                throw new \Exception("'$name' option does not exist");
            }
        }
        // Get the value
        return $this->$name;
    }
    
    /*
        Unset a value
        
        Parameters
            string $name    : the option name
        
        Throw
            Exception       : because the method is disabled
    */
    public function offsetUnset($name){
        throw new \Exception("Unset behavior is disabled");
    }

}
