<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Version : 0.2
    Author  : Aurélien Delogu <dev@dreamysource.fr>
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
class Chernozem implements ArrayAccess, Iterator, Serializable{
    
    /*
        array $_values  : injected values
        array $_locks   : locked values
        array $_types   : value types
        array $_setters : value setters
        array $_getters : value getters
    */
    protected $_values  = array();
    protected $_locks   = array();
    protected $_types   = array();
    protected $_setters = array();
    protected $_getters = array();
    
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
        Lock a value to prevent overwrites
        
        string $key : a value's key
        
        Return      : Chernozem
    */
    public function lock($key){
        $this->_locks[]=(string)$key;
        return $this;
    }
    
    /*
        Declare a value to specific types
        
        string $key             : a value's key
        array, string $types    : variable types
        
        Return                  : Chernozem
    */
    public function hint($key,$types){
        $types=(array)$types;
        $this->_types[(string)$key]=$types;
        return $this;
    }
    
    /*
        Set the object result of a closure to be persistent
        
        string $key : a value's key
        
        Return      : Chernozem
    */
    public function persist($key){
        if(($closure=$this->_values[$key]) instanceof Closure){
            $this->_values[$key]=function($chernozem) use ($closure){
                static $value;
                if($value===null){
                    $value=$closure($chernozem);
                }
                return $value;
            };
        }
        return $this;
    }
    
    /*
        Prevent a closure to be executed
        
        string $key : a value's key
        
        Return      : Chernozem
    */
    public function integrate($key){
        if(($closure=$this->_values[$key]) instanceof Closure){
            $this->_values[$key]=function($chernozem) use ($closure){
                return $closure;
            };
        }
        return $this;
    }
    
    /*
        Surchage the setter
        
        string $key         : a value's key
        Closure $closure    : the closure
        
        Return              : Chernozem
    */
    public function setter($key,Closure $closure){
        $this->_setters[(string)$key]=$closure;
        return $this;
    }
    
    /*
        Overload the getter
        
        string $key         : a value's key
        Closure $closure    : the closure
        
        Return              : Chernozem
    */
    public function getter($key,Closure $closure){
        $this->_getters[(string)$key]=$closure;
        return $this;
    }
    
    /*
        Return the value list
        
        Return: array
    */
    public function toArray(){
        return $this->_values;
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
        // Verify
        if(!$key and $key!==0){
            throw new Exception("Expects a non empty key");
        }
        if(array_search($key,$this->_locks)){
            throw new Exception("'$key' value is locked");
        }
        // Verify the type
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
                    // Real object
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
        // Create a new Chernozem object for that array
        if(is_array($value)){
            $value=new self($value);
        }
        // Execute the setter
        if($setter=$this->_setters[$key]){
            $value=$setter($value);
        }
        // Register the value
        $this->_values[$key]=$value;
    }
    
    /*
        Return a value
        
        string $key : the key
        
        Return      : mixed
    */
    public function offsetGet($key){
        $value=$this->_values[(string)$key];
        // Execute the getter
        if($getter=$this->_getters[$key]){
            $value=$getter($value);
        }
        // Execute the closure...
        if($value instanceof Closure){
            return $value($this);
        }
        // ...Or return the value
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
            if($value instanceof Closure){
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
