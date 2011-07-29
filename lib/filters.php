<?php

/*
    Some useful filters for Chernozem

    Author
        Aurélien Delogu (dev@dreamysource.fr)
*/

/*
    Lock a value to prevent overwrites

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_lock;
$chernozem_lock=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            throw new Exception("'$key' value is locked");
        }
    );
};

/*
    Persist values for services

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_persist;
$chernozem_persist=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($closure){
            if($closure instanceof Closure){
                $closure=function($chernozem) use($closure){
                    static $persistent;
                    if($persistent===null){
                        $persistent=$closure($chernozem);
                    }
                    return $persistent;
                };
            }
            return $closure;
        }
    );
};

/*
    Integer type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_integer;
$chernozem_integer=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_int($value)){
                throw new Exception("'$key' value is not an integer");
            }
            return $value;
        }
    );
};

/*
    Float type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_float;
$chernozem_float=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_float($value)){
                throw new Exception("'$key' value is not a float number");
            }
            return $value;
        }
    );
};

/*
    Boolean type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_boolean;
$chernozem_boolean=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_bool($value)){
                throw new Exception("'$key' value is not a boolean value");
            }
            return $value;
        }
    );
};

/*
    String type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_string;
$chernozem_string=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_string($value)){
                throw new Exception("'$key' value is not a string");
            }
            return $value;
        }
    );
};

/*
    Numeric type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_numeric;
$chernozem_numeric=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_numeric($value)){
                throw new Exception("'$key' value is not a numeric value");
            }
            return $value;
        }
    );
};

/*
    Array type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_array;
$chernozem_array=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_array($value)){
                throw new Exception("'$key' value is not an array");
            }
            return $value;
        }
    );
};

/*
    Object type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
        string $type        : specific object type
*/
global $chernozem_object;
$chernozem_object=function($chernozem,$key,$type=null){
    $chernozem->filter(
        $key,
        function($key,$value) use($type){
            $type=(string)$type;
            if($type and !($value instanceof $type)){
                throw new Exception("'$key' value is not a $type object");
            }
            if(!$type and !is_object($value)){
                throw new Exception("'$key' value is not an object");
            }
            return $value;
        }
    );
};

/*
    Callable type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_callable;
$chernozem_callable=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_callable($value)){
                throw new Exception("'$key' value is not callable");
            }
            return $value;
        }
    );
};

/*
    Resource type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
global $chernozem_resource;
$chernozem_resource=function($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_resource($value)){
                throw new Exception("'$key' value is not a resource");
            }
            return $value;
        }
    );
};
