<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Version : 0.6.0
    Author  : Aurélien Delogu (dev@dreamysource.fr)
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
class Chernozem implements ArrayAccess, Iterator, Serializable, Countable{
    
    /*
        int FILTER_SET      : the set filter type
        int FILTER_GET      : the get filter type
        int FILTER_UNSET    : the unset filter type
    */
    const FILTER_SET    = 0;
    const FILTER_GET    = 1;
    const FILTER_UNSET  = 2;

    /*
        array $__values     : injected values
        array $__filters    : filters list
    */
    protected $__values     = array();
    protected $__filters;

    /*
        Constructor
        
        Parameters
            array, Chernozem $values: a value list to fill in the container
    */
    public function __construct($values=array()){
        if(is_array($values) or ($values instanceof self)){
            foreach($values as $key=>$value){
                $this->offsetSet($key,$value);
            }
        }
        $this->__filters=array(
            self::FILTER_SET    => array(),
            self::FILTER_GET    => array(),
            self::FILTER_UNSET  => array()
        );
    }
    
    /*
        Set a filter for a value
        
        Parameters
            string, int $key    : a value's key
            Closure $closure    : the closure to run for that key
        
        Return
            Chernozem
    */
    public function filter($key,Closure $closure,$type=0){
        switch($type=(int)$type){
            case self::FILTER_SET:
            case self::FILTER_GET:
            case self::FILTER_UNSET:
                break;
            default:
                throw new Exception("The filter type must be one of FILTER_SET, FILTER_GET or FILTER_UNSET");
        }
        $this->__filters[$type][$this->__formatKey($key)]=$closure;
        return $this;
    }
    
    /*
        Search a value in the container

        Parameters
            string $value           : the value to find

        Return
            boolean, int, string    : false if not found, otherwise the key
    */
    public function search($value){
        return array_search($value,$this->__values,true);
    }

    /*
        Convert Chernozem to an array
        
        Return
            array
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
        Return the number of values in the container

        Return
            int
    */
    public function count(){
        return count($this->__values);
    }
    
    /*
        Verify if the key exists
        
        Parameters
            string, int $key: the key
        
        Return
            boolean
    */
    public function offsetExists($key){
        return array_key_exists($this->__formatKey($key),$this->__values);
    }
    
    /*
        Set a value
        
        Parameters
            string, int $key    : the key
            mixed $value        : the value
    */
    public function offsetSet($key,$value){
        // Format
        if($key===null){
            $key=count($this->__values);
        }
        $key=$this->__formatKey($key);
        // Execute the filter
        if($filter=$this->__filters[self::FILTER_SET][$key]){
            $value=$filter($key,$value);
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
        
        Parameters
            string, int $key: the key
        
        Return
            mixed
    */
    public function offsetGet($key){
        // Format
        $key=$this->__formatKey($key);
        // Get the value
        $value=&$this->__values[$key];
        // Default value
        if($value===null){
            $value=new self;
        }
        // Execute the filter
        if($filter=$this->__filters[self::FILTER_GET][$key]){
            $value=$filter($key,$value);
        }
        return $value;
    }
    
    /*
        Unset a value
        
        Parameters
            string, int $key: the key
    */
    public function offsetUnset($key){
        // Init
        $key=$this->__formatKey($key);
        $unset=true;
        // Execute the filter
        if($filter=$this->__filters[self::FILTER_UNSET][$key]){
            $unset=(bool)$filter($key,$this->__values[$key]);
        }
        // Unset the value
        if($unset){
            unset($this->__values[$key]);
        }
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
        Serialize Chernozem
        
        Return
            string
    */
    public function serialize(){
        // Prepare data
        $data=get_object_vars($this);
        $arrays=array(
            &$data['__values'],
            &$data['__filters'][self::FILTER_SET],
            &$data['__filters'][self::FILTER_GET],
            &$data['__filters'][self::FILTER_UNSET]
        );
        // Serialize closures
        foreach($arrays as &$array){
            foreach($array as &$value){
                if($value instanceof Closure){
                    $value=serialize_closure($value);
                }
            }
        }
        // Final serialization
        return serialize($data);
    }
    
    /*
        Unserialize Chernozem
        
        Parameters
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
        $data['__filters'][self::FILTER_SET]=$unserialize($data['__filters'][self::FILTER_SET]);
        $data['__filters'][self::FILTER_GET]=$unserialize($data['__filters'][self::FILTER_GET]);
        $data['__filters'][self::FILTER_UNSET]=$unserialize($data['__filters'][self::FILTER_UNSET]);
        // Dump final data
        foreach($data as $name=>$value){
            $this->$name=$value;
        }
    }
    
    /*
        Verify and format a key

        Parameters
            string $key: the key

        Return
            string: the formatted key
    */
    protected function __formatKey($key){
        // Verify type
        if(!is_string($key) and !is_int($key) and !is_object($key)){
            throw new Exception("Key must be a string, an integer or an object");
        }
        // Verify strings
        if($key===''){
            throw new Exception("Key string can't be empty");
        }
        // Format key objects
        if(is_object($key)){
            $key=spl_object_hash($key);
        }
        return $key;
    }

}
