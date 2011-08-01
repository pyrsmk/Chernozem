<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require(__DIR__.'/../lib/Unit/Suite.php');
require(__DIR__.'/../lib/Unit/Suite/Cli.php');

require(__DIR__.'/../lib/serialize.php');

require(__DIR__.'/../src/filters.php');
require(__DIR__.'/../src/Chernozem.php');

$suite=new Lumy\Unit\Suite\Cli('Chernozem');

$suite->test('Basics',12,function() use ($suite){
    // Constructor
    $chernozem=new Chernozem(array('test'=>'test'));
    $suite->check('Array passed to constructor',$chernozem['test']=='test');
    $chernozem=new Chernozem($chernozem);
    $suite->check('Chernozem object passed to constructor',$chernozem['test']=='test');
    // Set/get
    $chernozem=new Chernozem;
    $chernozem['test']=33;
    $suite->check('Set/get',$chernozem['test']==33);
    try{
        $chernozem['']=33;
        $suite->check("Set/get: empty key",false);
    }
    catch(Exception $e){
        $suite->check("Set/get: empty key",true);
    }
    $chernozem[]='test2';
    $suite->check('Set/get: [] method',$chernozem[1]=='test2');
    $chernozem[$chernozem]='bar';
    $suite->check('Set/get: objects as key',$chernozem[$chernozem]=='bar');
    $chernozem['test']=function(){};
    $suite->check('Set/get: closures',$chernozem['test'] instanceof Closure);
    // Isset/unset
    $suite->check('Isset',isset($chernozem['test']));
    unset($chernozem['test']);
    $suite->check('Unset',!isset($chernozem['test']));
    // Other
    $chernozem['test']='test';
    $suite->check('Search: string index',$chernozem->search('test')=='test');
    $suite->check('Search: numeric index',$chernozem->search('test2')==1);
    $suite->check('Count',count($chernozem)==3);
})

->test('Filters',9,function() use ($suite){
    $chernozem=new Chernozem;
    // FILTER_SET
    $chernozem->filter(
        'test',
        function($key,$value) use($suite){
            $suite->check('Set: key',$key=='test');
            $suite->check('Set: value',$value instanceof Closure);
            return 72;
        },
        $chernozem::FILTER_SET
    );
    $chernozem['test']=function(){};
    $suite->check('Set: equals to 72',$chernozem['test']==72);
    // FILTER_GET
    $chernozem->filter(
        'test',
        function($key,$value) use($suite){
            $suite->check('Get: key',$key=='test');
            $suite->check('Get: value',$value==72);
            return $value;
        },
        $chernozem::FILTER_GET
    );
    $suite->check('Get: equals to 72',$chernozem['test']==72);
    // FILTER_UNSET
    $chernozem['test2']=72;
    $chernozem->filter(
        'test2',
        function($key,$value) use($suite){
            $suite->check('Unset: key',$key=='test2');
            $suite->check('Unset: value',$value==72);
        },
        $chernozem::FILTER_UNSET
    );
    unset($chernozem['test2']);
    $suite->check('Unset: still setted',$chernozem['test2']==72);
})

->test('Iteration',10,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test1']=1;
    $chernozem['test2']=2;
    $chernozem['test3']=3;
    $chernozem['test4']=4;
    $chernozem['test5']=5;
    $i=0;
    foreach($chernozem as $key=>$value){
        $suite->check('Key '.(++$i),$key=="test$i");
        $suite->check('Value '.$i,$value==$i);
    }
})

->test('Serialization',2,function() use ($suite){
    // Prepare tests
    $chernozem=new Chernozem;
    $chernozem['basic']=function(){};
    $chernozem->filter('filter',function($key,$value){
        return ucfirst($value);
    });
    $chernozem['filter']='test';
    // (Un)serialize
    $chernozem=unserialize(serialize($chernozem));
    // Tests
    $suite->check('Values',$chernozem['basic'] instanceof Closure);
    $suite->check('Filters',$chernozem['filter']=='Test');
})

->test('toArray',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['array']=array();
    $array=$chernozem->toArray();
    $suite->check('Valid array',is_array($array));
    $suite->check('Valid recursive array',is_array($array['array']));
})

->test('Built-in filters',24,function() use ($suite){
    // Lock
    $chernozem=new Chernozem(array('foo'=>'bar'));
    global $chernozem_lock;
    $chernozem_lock($chernozem,'foo');
    try{
        $chernozem['foo']='foobar';
        $suite->check('Lock: set',false);
    }
    catch(Exception $e){
        $suite->check('Lock: set',true);
    }
    try{
        unset($chernozem['foo']);
        $suite->check('Lock: unset',false);
    }
    catch(Exception $e){
        $suite->check('Lock: unset',true);
    }
    // Service
    $chernozem=new Chernozem(array(
        'foo' => function($chernozem){
            return 'bar';
        }
    ));
    global $chernozem_service;
    $chernozem_service($chernozem,'foo');
    $suite->check('Service',$chernozem['foo']=='bar');
    // Persist
    $chernozem=new Chernozem;
    global $chernozem_persist;
    $chernozem_persist($chernozem,'foo');
    $chernozem['foo']=function($chernozem){
        return time();
    };
    $suite->check('Persist',$chernozem['foo']==$chernozem['foo']);
    // Integer
    $chernozem=new Chernozem();
    global $chernozem_integer;
    $chernozem_integer($chernozem,'foo');
    try{
        $chernozem['foo']=72;
        $suite->check('Integer: match',true);
    }
    catch(Exception $e){
        $suite->check('Integer: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Integer: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Integer: do not match',true);
    }
    // Float
    $chernozem=new Chernozem();
    global $chernozem_float;
    $chernozem_float($chernozem,'foo');
    try{
        $chernozem['foo']=72.5;
        $suite->check('Float: match',true);
    }
    catch(Exception $e){
        $suite->check('Float: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Float: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Float: do not match',true);
    }
    // Boolean
    $chernozem=new Chernozem();
    global $chernozem_boolean;
    $chernozem_boolean($chernozem,'foo');
    try{
        $chernozem['foo']=true;
        $suite->check('Boolean: match',true);
    }
    catch(Exception $e){
        $suite->check('Boolean: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Boolean: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Boolean: do not match',true);
    }
    // String
    $chernozem=new Chernozem();
    global $chernozem_string;
    $chernozem_string($chernozem,'foo');
    try{
        $chernozem['foo']='bar';
        $suite->check('String: match',true);
    }
    catch(Exception $e){
        $suite->check('String: match',false);
    }
    try{
        $chernozem['foo']=72;
        $suite->check('String: do not match',false);
    }
    catch(Exception $e){
        $suite->check('String: do not match',true);
    }
    // Numeric
    $chernozem=new Chernozem();
    global $chernozem_numeric;
    $chernozem_numeric($chernozem,'foo');
    try{
        $chernozem['foo']='72';
        $suite->check('Numeric: match',true);
    }
    catch(Exception $e){
        $suite->check('Numeric: match',false);
    }
    try{
        $chernozem['foo']='blabla';
        $suite->check('Numeric: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Numeric: do not match',true);
    }
    // Array
    $chernozem=new Chernozem();
    global $chernozem_array;
    $chernozem_array($chernozem,'foo');
    try{
        $chernozem['foo']=array();
        $suite->check('Array: match',true);
    }
    catch(Exception $e){
        $suite->check('Array: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Array: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Array: do not match',true);
    }
    // Object
    $chernozem=new Chernozem();
    global $chernozem_object;
    $chernozem_object($chernozem,'foo');
    try{
        $chernozem['foo']=new stdClass;
        $suite->check('Object: match',true);
    }
    catch(Exception $e){
        $suite->check('Object: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Object: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Object: do not match',true);
    }
    // Specific object
    $chernozem=new Chernozem();
    global $chernozem_object;
    $chernozem_object($chernozem,'foo','Chernozem');
    try{
        $chernozem['foo']=new Chernozem;
        $suite->check('Object (specific): match',true);
    }
    catch(Exception $e){
        $suite->check('Object (specific): match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Object (specific): do not match',false);
    }
    catch(Exception $e){
        $suite->check('Object (specific): do not match',true);
    }
    // Callable
    $chernozem=new Chernozem();
    global $chernozem_callable;
    $chernozem_callable($chernozem,'foo');
    try{
        $chernozem['foo']=array($chernozem,'search');
        $suite->check('Callable: match',true);
    }
    catch(Exception $e){
        $suite->check('Callable: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Callable: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Callable: do not match',true);
    }
    // Resource
    $chernozem=new Chernozem();
    global $chernozem_resource;
    $chernozem_resource($chernozem,'foo');
    try{
        $chernozem['foo']=curl_init();
        $suite->check('Resource: match',true);
    }
    catch(Exception $e){
        $suite->check('Resource: match',false);
    }
    try{
        $chernozem['foo']='bar';
        $suite->check('Resource: do not match',false);
    }
    catch(Exception $e){
        $suite->check('Resource: do not match',true);
    }
})

->run();
