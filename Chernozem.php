<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Version : 4.0
    Author  : Aurélien Delogu <dev@dreamysource.fr>
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
class Chernozem implements ArrayAccess, Iterator, Serializable{
    
    /*
        array $__values     : injected values
        array $__filters    : filters list
        array $__services   : the services list
    */
    protected $__values     = array();
    protected $__filters    = array();
    protected $__services   = array();

    /*
        Constructor
        
        array $values: a value list to fill in the container
    */
    public function __construct(array $values=array()){
        if($values){
            foreach($values as $key=>$value){
                $this->offsetSet($key,$value);
            }
        }
    }
    
    /*
        Set a filter for a value
        
        string, int $key    : a value's key
        Closure $closure    : the closure to run for that key
        
        Return              : Chernozem
    */
    public function filter($key,Closure $closure){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $this->__filters[$key]=$closure;
        return $this;
    }

    /*
        Set a closure as a service
        
        string, int $key    : a value's key
        
        Return              : Chernozem
    */
    public function service($key){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        if(($closure=$this->__values[$key]) instanceof Closure){
            $this->__services[]=$key;
        }
        return $this;
    }

    /*
        Convert Chernozem to an array
        
        Return: array
    */
    public function toArray(){
        $data=array();
        foreach($this as $key=>$value){
            if($value instanceof Chernozem){
                $data[$key]=$value->toArray();
            }
            else{
                $data[$key]=$value;
            }
        }
        return $data;
    }
    
    /*
        Verify if the key exists
        
        string, int $key    : the key
        
        Return              : boolean
    */
    public function offsetExists($key){
        return array_key_exists($key,$this->__values);
    }
    
    /*
        Set a value
        
        string, int $key    : the key
        mixed $value        : the value
    */
    public function offsetSet($key,$value){
        // Key verification
        if(!$key and $key!==0){
            throw new Exception("Expects a non empty key");
        }
        // Execute the filter
        if($filter=$this->__filters[$key]){
            $value=$filter($value);
        }
        // Create a new Chernozem object for that array
        if(is_array($value)){
            $value=new self($value);
        }
        // Register the value
        $this->__values[$key]=$value;
    }
    
    /*
        Return a value
        
        string, int $key    : the key
        
        Return              : mixed
    */
    public function offsetGet($key){
        $value=$this->__values[$key];
        // Execute the service...
        if(in_array($key,$this->__services)){
            return $value($this);
        }
        // ...Or just return the value
        return $value;
    }
    
    /*
        Unset a value
        
        string, int $key: the key
    */
    public function offsetUnset($key){
        if(in_array($key,$this->__locks)){
            throw new Exception("'$key' value is locked");
        }
        unset($this->__values[$key]);
    }
    
    /*
        Return the current value of the container
        
        Return: mixed
    */
    public function current(){
        return current($this->__values);
    }
    
    /*
        Return the current key of the container
        
        Return: string
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
        
        Return: boolean
    */
    public function valid(){
        return (bool)key($this->__values);
    }
    
    /*
        Serialize Chernozem
        
        Return: string
    */
    public function serialize(){
        // Prepare data
        $data=get_object_vars($this);
        // Serialize closures
        foreach(array('__values','__filters') as $name){
            $values=array();
            foreach($data[$name] as $key=>$value){
                if($value instanceof Closure){
                    $values[$key]=serialize_closure($value);
                }
                else{
                    $values[$key]=$value;
                }
            }
            $data[$name]=$values;
        }
        // Final serialization
        return serialize($data);
    }
    
    /*
        Unserialize Chernozem
        
        string $serialized: serialized data
    */
    public function unserialize($serialized){
        // Unserialize data
        $data=unserialize($serialized);
        // Unserialize closures
        $unserialize=function($array){
            foreach($array as $key=>$value){
                if(is_string($value) and strpos($value,':"function(')!==false){
                    $values[$key]=unserialize_closure($value);
                }
                else{
                    $values[$key]=$value;
                }
            }
            return $values;
        };
        $data['__values']=$unserialize($data['__values']);
        $data['__filters']=$unserialize($data['__filters']);
        // Dump final data
        foreach($data as $name=>$value){
            $this->$name=$value;
        }
    }
    
}
