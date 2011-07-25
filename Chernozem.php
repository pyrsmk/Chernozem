<?php

/*
    An advanced dependency injection container inspired from Pimple
    
    Version : 0.3.2
    Author  : Aurélien Delogu <dev@dreamysource.fr>
    URL     : https://github.com/pyrsmk/Chernozem
    License : MIT
*/
class Chernozem implements ArrayAccess, Iterator, Serializable{
    
    /*
        array $__values     : injected values
        array $__locks      : locked values
        array $__types      : value types
        array $__services   : executable closures list
        array $__setters    : setters for values
        array $__getters    : getters for values
    */
    protected $__values     = array();
    protected $__locks      = array();
    protected $__types      = array();
    protected $__services   = array();
    protected $__setters    = array();
    protected $__getters    = array();
    
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
        $this->__locks[]=$key;
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
        $this->__types[$key]=(array)$types;
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
        Set the object result of a closure to be persistent
        
        string, int $key    : a value's key
        
        Return              : Chernozem
    */
    public function persist($key){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        if(($closure=$this->__values[$key]) instanceof Closure){
            if(!in_array($key,$this->__services)){
                throw new Exception("The closure must be set as a service to become persistent");
            }
            $this->__values[$key]=function($chernozem) use ($closure){
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
        Surchage the setter
        
        string, int $key    : a value's key
        Closure $closure    : the closure
        
        Return              : Chernozem
    */
    public function setter($key,Closure $closure){
        if(!is_string($key) and !is_int($key)){
            throw new Exception("The provided key must be a string or an integer");
        }
        $this->__setters[$key]=$closure;
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
        $this->__getters[$key]=$closure;
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
        // Verify
        if(!$key and $key!==0){
            throw new Exception("Expects a non empty key");
        }
        if(in_array($key,$this->__locks)){
            throw new Exception("'$key' value is locked");
        }
        // Verify the type
        if($this->__types[$key]){
            $ok=false;
            foreach($this->__types[$key] as $type){
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
        if($setter=$this->__setters[$key]){
            $value=$setter($value);
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
        // Execute the getter
        if($getter=$this->__getters[$key]){
            $value=$getter($value);
        }
        // Execute the closure...
        if(in_array($key,$this->__services)){
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
        foreach(array('__values','__setters','__getters') as $name){
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
        $data['__setters']=$unserialize($data['__setters']);
        $data['__getters']=$unserialize($data['__getters']);
        // Dump final data
        foreach($data as $name=>$value){
            $this->$name=$value;
        }
    }
    
}
