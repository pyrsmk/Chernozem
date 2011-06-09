<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Author: Aurélien Delogu <dev@dreamysource.fr>
*/
class Chernozem implements ArrayAccess, Iterator, Serializable{
    
    /*
        array $_values  : injected values
        array $_locks   : locked values
        array $_types   : values types
    */
    protected $_values  = array();
    protected $_locks   = array();
    protected $_types   = array();
    
    public function __construct(array $values=array()){
        if($values){
            foreach($values as $key=>$value){
                $this->offsetSet($key,$value);
            }
        }
    }
    
    /*
        Lock a value to prevent overwrites
        
        string $key : a value's key
    */
    public function lock($key){
        $this->_locks[]=(string)$key;
    }
    
    /*
        Declare a value to specific types
        
        string $key : a value's key
    */
    public function hint($key,array $types){
        $this->_types[(string)$key]=$types;
    }
    
    /*
        Set the object result of a closure to be persistent
        
        Closure $closure    : the closure
        
        Return              : Closure
    */
    public function persist(\Closure $closure){
        return function($chernozem) use ($closure){
            static $value;
            if($value===null){
                $value=$closure($chernozem);
            }
            return $value;
        };
    }
    
    /*
        Prevent a closure to be interpreted as a service
        
        Closure $closure    : the closure
        
        Return              : Closure
    */
    public function integrate(\Closure $closure){
        return function($chernozem) use ($closure){
            return $closure;
        };
    }
    
    /*
        Verify if the key exists
        
        string $key : the key
        
        Return      : boolean
    */
    public function offsetExists($key){
        return array_key_exists((string)$key,$this->_values);
    }
    
    /*
        Set a value
        
        string $key     : the key
        mixed $value    : the value
    */
    public function offsetSet($key,$value){
        $key=(string)$key;
        if(!$key){
            throw new Exception("Expects a non empty key");
        }
        if(array_search($key,$this->_locks)){
            throw new Exception("'$key' value is locked");
        }
        if($this->_types[$key]){
            $ok=false;
            foreach($this->_types[$key] as $type){
                if(is_string($type)){
                    switch($type){
                        case 'int':
                        case 'integer':
                        case 'long':
                            $ok=is_int($value);
                            break;
                        case 'float':
                        case 'double':
                        case 'real':
                            $ok=is_float($value);
                            break;
                        case 'numeric':
                            $ok=is_numeric($value);
                            break;
                        case 'bool':
                        case 'boolean':
                            $ok=is_bool($value);
                            break;
                        case 'string':
                            $ok=is_string($value);
                            break;
                        case 'scalar':
                            $ok=is_scalar($value);
                            break;
                        case 'array':
                            $ok=is_array($value);
                            break;
                        case 'object':
                            $ok=is_object($value);
                            break;
                        case 'resource':
                            $ok=is_resource($value);
                            break;
                        case 'callable':
                            $ok=is_callable($value);
                            break;
                        default:
                            $ok=$value instanceof $type;
                    }
                }
                else{
                    if(!($value instanceof $type)){
                        $ok=true;
                       break; 
                    }
                }
                if($ok) break;
            }
            if(!$ok){
                throw new Exception("'$key' value doesn't match predefined types");
            }
        }
        if(is_array($value)){
            $value=new self($value);
        }
        $this->_values[$key]=$value;
    }
    
    /*
        Return a value
        
        string $key : the key
        
        Return      : mixed
    */
    public function offsetGet($key){
        $value=$this->_values[(string)$key];
        if($value instanceof \Closure){
            return $value($this);
        }
        return $value;
    }
    
    /*
        Unset a value
        
        string $key: the key
    */
    public function offsetUnset($key){
        $key=(string)$key;
        if(array_search($key,$this->_locks)){
            throw new Exception("'$key' value is locked");
        }
        unset($this->_values[$key]);
    }
    
    /*
        Return the current value of the container
        
        Return: mixed
    */
    public function current(){
        return current($this->_values);
    }
    
    /*
        Return the current key of the container
        
        Return: string
    */
    public function key(){
        return key($this->_values);
    }
    
    /*
        Advance the internal pointer of the container
    */
    public function next(){
        next($this->_values);
    }
    
    /*
        Reset the internal pointer of the container
    */
    public function rewind(){
        rewind($this->_values);
    }
    
    /*
        Verify if the current value is valid
        
        Return: boolean
    */
    public function valid(){
        return (bool)key($this->_values);
    }
    
    /*
        Serialize Chernozem
        
        Return: string
    */
    public function serialize(){
        foreach($this->_values as $key=>$value){
            if($value instanceof \Closure){
                $values[$key]=serialize_closure($value);
            }
            else{
                $values[$key]=$value;
            }
        }
        return serialize($values);
    }
    
    /*
        Unserialize Chernozem
        
        string $serialized: serialized data
    */
    public function unserialize($serialized){
        foreach(unserialize($serialized) as $key=>$value){
            if(($closure=@unserialize_closure($value)) instanceof Closure){
                $this->_values[$key]=$closure;
            }
            else{
                $this->_values[$key]=$value;
            }
        }
    }
    
}
