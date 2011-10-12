<?php

namespace Chernozem;

/*
    Container-oriented dependency injection manager
    
    Version : 1.0.0
    Author  : Aurélien Delogu (dev@dreamysource.fr)
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
abstract class Container implements \ArrayAccess, \Iterator, \Countable{

    /*
        array $__values: injected values
    */
    protected $__values=array();
    
    /*
        Constructor
        
        Parameters
            array $values: a value list to fill in the container
    */
    public function __construct($values=array()){
        if(is_array($values) or ($values instanceof \Traversable)){
            foreach($values as $key=>$value){
                $this->offsetSet($key,$value);
            }
        }
    }
    
    /*
        Return the contained values
        
        Return
            array
    */
    public function toArray(){ 
        return $this->__values;
    }
    
    /*
        Verify if the key exists
        
        Parameters
            string, int, object $key: the key
        
        Return
            boolean
    */
    public function offsetExists($key){
        return isset($this->__values[$this->__formatKey($key)]);
    }
    
    /*
        Set a value
        
        Parameters
            string, int, object $key    : the key
            mixed $value                : the value
    */
    public function offsetSet($key,$value){
        // Format
        if($key===null){
            $key=count($this->__values);
        }
        $key=$this->__formatKey($key);
        // Register the value
        $this->__values[$key]=$value;
    }
    
    /*
        Return a value
        
        Parameters
            string, int, object $key: the key
        
        Return
            mixed
    */
    public function offsetGet($key){
        // Format
        $key=$this->__formatKey($key);
        // Get the value
        $value=&$this->__values[$key];
        return $value;
    }
    
    /*
        Unset a value
        
        Parameters
            string, int, object $key: the key
    */
    public function offsetUnset($key){
        unset($this->__values[$this->__formatKey($key)]);
    }
    
    /*
        Return the current value of the container
        
        Return
            mixed
    */
    public function current(){
        return current($this->__values);
    }
    
    /*
        Return the current key of the container
        
        Return
            string
    */
    public function key(){
        return key($this->__values);
    }
    
    /*
        Advance the internal pointer of the container
    */
    public function next(){
        next($this->__values);
    }
    
    /*
        Reset the internal pointer of the container
    */
    public function rewind(){
        reset($this->__values);
    }
    
    /*
        Verify if the current value is valid
        
        Return
            boolean
    */
    public function valid(){
        return key($this->__values)!==null;
    }

    /*
        Return the number of values in the container

        Return
            int
    */
    public function count(){
        return count($this->__values);
    }
    
    /*
        Verify and format a key

        Parameters
            string, int, object $key    : the key

        Return
            string, int                 : the formatted key

        Throw
            Exception                   : if a key is an empty string
            Exception                   : if the key type is invalid
    */
    protected function __formatKey($key){
        // String
        if(is_string($key)){
            if($key){
                return $key;
            }
            else{
                throw new \Exception("Key string can't be empty");
            }
        }
        // Integer
        elseif(is_int($key)){
            return $key;
        }
        // Object
        elseif(is_object($key)){
            return spl_object_hash($key);
        }
        throw new \Exception("Key must be a string, an integer or an object");
    }
    
}
