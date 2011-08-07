<?php

/*
    An advanced dependency injection container
    
    Version : I.I
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
    protected $__values;
    protected $__filters=array();

    /*
        Constructor
        
        Parameters
            array $values: a value list to fill in the container
    */
    public function __construct(array $values=array()){
        $this->__values=$values;
    }
    
    /*
        Set a filter for a value
        
        Parameters
            string, int, object $key    : a value's key
            Closure $closure            : the closure to run for that key
        
        Return
            Chernozem
    */
    public function filter($key,Closure $closure,$type=self::FILTER_SET){
        $this->__filters[(int)$type][$this->__formatKey($key)]=$closure;
        return $this;
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
        // Execute the filter
        if(isset($this->__filters[self::FILTER_SET][$key])){
            $filter=$this->__filters[self::FILTER_SET][$key];
            $value=$filter($key,$value);
        }
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
        // Execute the filter
        if(isset($this->__filters[self::FILTER_GET][$key])){
            $filter=$this->__filters[self::FILTER_GET][$key];
            $value=$filter($key,$value);
        }
        return $value;
    }
    
    /*
        Unset a value
        
        Parameters
            string, int, object $key: the key
    */
    public function offsetUnset($key){
        // Init
        $key=$this->__formatKey($key);
        $unset=true;
        // Execute the filter
        if(isset($this->__filters[self::FILTER_UNSET][$key])){
            $filter=$this->__filters[self::FILTER_UNSET][$key];
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
        Return the number of values in the container

        Return
            int
    */
    public function count(){
        return count($this->__values);
    }

    /*
        Serialize Chernozem
        
        Return
            string
    */
    public function serialize(){
        // Serialize function
        $serialize=function($array) use(&$serialize){
            foreach($array as &$value){
                if($value instanceof Closure){
                    $value=serialize_closure($value);
                }
                elseif(is_array($value)){
                    $value=$serialize($value);
                }
            }
            return $array;
        };
        // Serialize data
        $data[0]=$serialize($this->__values);
        $data[1]=$serialize($this->__filters[self::FILTER_SET]);
        $data[2]=$serialize($this->__filters[self::FILTER_GET]);
        $data[3]=$serialize($this->__filters[self::FILTER_UNSET]);
        // Final serialization
        return serialize($data);
    }
    
    /*
        Unserialize Chernozem
        
        Parameters
            string $serialized: serialized data
    */
    public function unserialize($serialized){
        // Unserialize function
        $unserialize=function($array) use(&$unserialize){
            foreach($array as &$value){
                if(is_string($value) and strpos($value,':"function(')!==false){
                    $value=unserialize_closure($value);
                }
                elseif(is_array($value)){
                    $value=$unserialize($value);
                }
            }
            return $array;
        };
        // General unserialization
        $data=unserialize($serialized);
        // Unserialize data
        $this->__values=$unserialize($data[0]);
        $this->__filters[self::FILTER_SET]=$unserialize($data[1]);
        $this->__filters[self::FILTER_GET]=$unserialize($data[2]);
        $this->__filters[self::FILTER_UNSET]=$unserialize($data[3]);
    }
    
    /*
        Verify and format a key

        Parameters
            string, int, object $key    : the key

        Return
            string, int                 : the formatted key

        Throw
            Exception                   : if a key is a string and it's empty
            Exception                   : if the key type is invalid
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
