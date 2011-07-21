<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Version : 0.2.5
    Author  : Aurélien Delogu <dev@dreamysource.fr>
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
class Chernozem implements ArrayAccess, Iterator, Serializable{
    
    /*
        array $_values  : injected values
        array $_locks   : locked values
        array $_types   : value types
        array $_setters : setters for values
        array $_getters : getters for values
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
        
        string, int $key    : a value's key
        
        Return              : Chernozem
    */
    public function lock($key){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $this->_locks[]=$key;
        return $this;
    }
    
    /*
        Declare a value to specific types
        
        string, int $key        : a value's key
        array, string $types    : variable types
        
        Return                  : Chernozem
    */
    public function hint($key,$types){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $types=(array)$types;
        $this->_types[$key]=$types;
        return $this;
    }
    
    /*
        Set the object result of a closure to be persistent
        
        string, int $key    : a value's key
        
        Return              : Chernozem
    */
    public function persist($key){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
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
        
        string, int $key : a value's key
        
        Return      : Chernozem
    */
    public function integrate($key){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        if(($closure=$this->_values[$key]) instanceof Closure){
            $this->_values[$key]=function($chernozem) use ($closure){
                return $closure;
            };
        }
        return $this;
    }
    
    /*
        Surchage the setter
        
        string, int $key    : a value's key
        Closure $closure    : the closure
        
        Return              : Chernozem
    */
    public function setter($key,Closure $closure){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $this->_setters[$key]=$closure;
        return $this;
    }
    
    /*
        Overload the getter
        
        string, int $key    : a value's key
        Closure $closure    : the closure
        
        Return              : Chernozem
    */
    public function getter($key,Closure $closure){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $this->_getters[$key]=$closure;
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
        
        string, int $key    : the key
        
        Return              : boolean
    */
    public function offsetExists($key){
        return array_key_exists($key,$this->_values);
    }
    
    /*
        Set a value
        
        string, int $key    : the key
        mixed $value        : the value
    */
    public function offsetSet($key,$value){
        // Verify
        if(!$key and $key!==0){
            throw new Exception("Expects a non empty key");
        }
        if(in_array($key,$this->_locks)){
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
        
        string, int $key    : the key
        
        Return              : mixed
    */
    public function offsetGet($key){
        $value=$this->_values[$key];
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
        
        string, int $key: the key
    */
    public function offsetUnset($key){
        if(in_array($key,$this->_locks)){
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
        // Prepare data
        $data=get_object_vars($this);
        // Serialize closures
        foreach(array('_values','_setters','_getters') as $name){
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
        $data['_values']=$unserialize($data['_values']);
        $data['_setters']=$unserialize($data['_setters']);
        $data['_getters']=$unserialize($data['_getters']);
        // Dump final data
        foreach($data as $name=>$value){
            $this->$name=$value;
        }
    }
    
}
