<?php

/*
    Serialize a closure
    
    Author              : Sami Greenbury
    
    Closure $closure    : the closure
    
    Return              : string
*/
function serialize_closure(\Closure $closure){
    $reflection=new ReflectionFunction($closure);
    $file=new SplFileObject($reflection->getFileName());
	$file->seek($reflection->getStartLine()-1);
	$end=$reflection->getEndLine();
	do{
		$code.=$file->current();
		$file->next();
	}
	while($file->key()<$end);
	$begin=strpos($code,'function');
	$end=strrpos($code,'}');
	$code=substr($code,$begin,$end-$begin+1);
	$context=$reflection->getStaticVariables();
	foreach($context as &$value){
	    if($value instanceof Closure){
	        $value=serialize_closure($value);
	    }
	}
	return serialize(array($code,$context));
}

/*
    Unserialize a closure
    
    Author          : Sami Greenbury
    
    string $closure : the serialized closure
    
    Return          : Closure
*/
function unserialize_closure($closure){
    list($code,$context)=unserialize((string)$closure);
    if(strpos($code,'function')!==0){
        trigger_error("Expects a serialized closure",E_USER_WARNING);
    }
    else{
        foreach($context as &$value){
            list($context_code,$context_context)=unserialize($value);
	        if(strpos($context_code,'function')===0){
	            $value=unserialize_closure($value);
	        }
	    }
	    extract($context);
	    eval("\$_closure=$code;");
	    return $_closure;
	}
}
