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
function chernozem_lock($chernozem,$key){
    $chernozem->filter(
        $key,
        $closure=function($key,$value){
            throw new Exception("'$key' value is locked");
        },
        $chernozem::FILTER_SET
    );
    $chernozem->filter(
        $key,
        $closure,
        $chernozem::FILTER_UNSET
    );
}

/*
    Will execute the closure and return the result

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_service($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value) use($chernozem){
            return $value($chernozem);
        },
        $chernozem::FILTER_GET
    );
}

/*
    Persist values for services

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_persist($chernozem,$key){
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
        },
        $chernozem::FILTER_SET
    );
}

/*
    Integer type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_integer($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_int($value)){
                throw new Exception("'$key' value is not an integer");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Float type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_float($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_float($value)){
                throw new Exception("'$key' value is not a float number");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Boolean type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_boolean($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_bool($value)){
                throw new Exception("'$key' value is not a boolean value");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    String type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_string($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_string($value)){
                throw new Exception("'$key' value is not a string");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Numeric type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_numeric($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_numeric($value)){
                throw new Exception("'$key' value is not a numeric value");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Array type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_array($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_array($value)){
                throw new Exception("'$key' value is not an array");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Object type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
        string $type        : specific object type
*/
function chernozem_object($chernozem,$key,$type=null){
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
        },
        $chernozem::FILTER_SET
    );
}

/*
    Callable type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_callable($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_callable($value)){
                throw new Exception("'$key' value is not callable");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}

/*
    Resource type-hinting

    Parameters
        object $chernozem   : a Chernozem child object
        int, string $key    : the key
*/
function chernozem_resource($chernozem,$key){
    $chernozem->filter(
        $key,
        function($key,$value){
            if(!is_resource($value)){
                throw new Exception("'$key' value is not a resource");
            }
            return $value;
        },
        $chernozem::FILTER_SET
    );
}
