<?php

/*
    An advanced dependency injection container

    Version : 2.3.0
    Author  : Aurélien Delogu (dev@dreamysource.fr)
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
abstract class Chernozem implements ArrayAccess, Iterator, Countable{

    /*
        array $__values             : container's values
        boolean $__container        : true to enable container mode (default: true)
        boolean $__properties       : true to enable properties mode (default: true)
        boolean $__traversable      : true if traversable (default: true)
        boolean $__nullable         : true to not throw an exception if a key doesn't exist (default: false)
        boolean $__locked           : true to lock object edition (default: false)
        array $__services           : service list
        array $__persistent_values  : persistent value list
    */
    protected $__values             = array();
    protected $__container          = true;
    protected $__properties         = true;
    protected $__traversable        = true;
    protected $__nullable           = false;
    protected $__locked             = false;
    protected $__services           = array();
    protected $__persistent_values  = array();

    /*
        Constructor

        Parameters
            array, object $values: a value list to fill in the container
    */
    public function __construct($values=array()){
        if(is_array($values) or ($values instanceof Traversable)){
            foreach($values as $name=>$value){
                $this->offsetSet($name,$value);
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
        Make a closure a service

        Parameters
            string, int, object $key

        Return
            Chernozem
    */
    public function service($key){
        if($this->__locked){
            throw new Exception("Object is locked, can't set '$key' as service");
        }
        $this->__services[$this->__formatKey($key)]=1;
        return $this;
    }

    /*
        Make a service a simple closure (and, by extension, non persistent)

        Parameters
            string, int, object $key

        Return
            Chernozem
    */
    public function unservice($key){
        if($this->__locked){
            throw new Exception("Object is locked, can't unset '$key' as service");
        }
        unset($this->__services[$this->__formatKey($key)]);
        return $this;
    }

    /*
        Verify if the option exists

        Parameters
            string, int, object $key

        Return
            boolean
    */
    public function offsetExists($key){
        // Init flag
        $exists=false;
        // Properties
        if(is_string($key) && $this->__properties){
            $exists=$exists || property_exists($this,$key) || property_exists($this,'_'.$key);
        }
        // Container
        if($this->__container){
            $exists=$exists || array_key_exists($this->__formatKey($key),$this->__values);
        }
        return $exists;
    }

    /*
        Set a value

        Parameters
            string, int, object $key
            mixed $value

        Throw
            Exception: if the option is locked
            Exception: if the provided option name's type is invalid
            Exception: if unable to set the value
    */
    public function offsetSet($key,$value){
        if($this->__locked){
            throw new Exception("Object is locked, can't set '$key' value");
        }
        // Init flag
        $registered=false;
        // Properties
        if(is_string($key) && $this->__properties){
            // Property exists
            if(property_exists($this,$key)){
                // Verify option type
                if(($type1=gettype($this->$key))!=($type2=gettype($value)) && $type1!='NULL'){
                    throw new Exception("Bad '$key' option's value type, $type1 expected but $type2 provided");
                }
                // Register the value
                $this->$key=$value;
                $registered=true;
            }
            // Property locked
            elseif(property_exists($this,'_'.$key)){
                throw new Exception("'$key' option is locked");
            }
        }
        // Container
        if($this->__container){
            // Format
            if($key===null){
                $key=count($this->__values);
            }
            $key=$this->__formatKey($key);
            // Register the value
            $this->__values[$key]=$value;
            $registered=true;
        }
        // Boom!
        if(!$registered){
            if(is_string($key)){
                throw new Exception("Unable to set '$key' value");
            }
            else{
                throw new Exception("Unable to set a value");
            }
        }
    }

    /*
        Return a value

        Parameters
            string, int, object $key

        Return
            mixed

        Throw
            Exception: if unable to get a value
    */
    public function offsetGet($key){
        // Properties
        if(is_string($key) && $this->__properties){
            // Property exists
            if(property_exists($this,$key)){
                $value=$this->$key;
            }
            // Property locked
            elseif(property_exists($this,'_'.$key)){
                $key='_'.$key;
                $value=$this->$key;
            }
        }
        // Container
        if($this->__container && array_key_exists($key=$this->__formatKey($key),$this->__values)){
            $value=$this->__values[$key];
        }
        // Service
        if($value instanceof Closure && $this->__services[$key]){
            if(is_null($val=&$this->__persistent_values[$key])){
                $val=$value();
            }
            $value=$val;
        }
        // Boom!
        if($value===null && !$this->__nullable){
            if(is_string($key)){
                throw new Exception("Unable to return '$key' value");
            }
            else{
                throw new Exception("Unable to return a value");
            }
        }
        return $value;
    }

    /*
        Unset a value

        Parameters
            string, int, object $key
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

        Throw
            Exception: if not traversable
    */
    public function rewind(){
        if(!$this->__traversable){
            throw new Exception("Container is not traversable");
        }
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
            string, int, object $key

        Return
            string, int

        Throw
            Exception: if a key is an empty string
            Exception: if the key type is invalid
    */
    protected function __formatKey($key){
        // String
        if(is_string($key)){
            if($key){
                return $key;
            }
            else{
                throw new Exception("Key string can't be empty");
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
        throw new Exception("Key must be a string, an integer or an object");
    }

}
