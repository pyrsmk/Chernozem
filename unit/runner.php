<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require(__DIR__.'/Unit/Suite.php');
require(__DIR__.'/Unit/Suite/Cli.php');

require(__DIR__.'/../serialize.php');
require(__DIR__.'/../Chernozem.php');

$suite=new Lumy\Unit\Suite\Cli('Chernozem');

$suite->test('Dependency injection',5,function() use ($suite){
    $chernozem=new Chernozem;
    try{
        $chernozem['']=33;
        $suite->check("Can't set an empty key",false);
    }
    catch(Exception $e){
        $suite->check("Can't set an empty key",true);
    }
    $chernozem['test']=33;
    $suite->check('Set/get',$chernozem['test']==33);
    $suite->check('Isset',isset($chernozem['test']));
    unset($chernozem['test']);
    $suite->check('Unset',!isset($chernozem['test']));
    $chernozem['test']=function(){};
    $suite->check('Closures',$chernozem['test'] instanceof Closure);
})

->test('Filter',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem->filter('test',function($value) use($suite){
        $suite->check('Passed value',$value instanceof Closure);
        return 72;
    });
    $chernozem['test']=function(){};
    $suite->check('Equals to 72',$chernozem['test']==72);
})

->test('Service',1,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){return 72;};
    $chernozem->service('test');
    $suite->check('Return 72',$chernozem['test']==72);
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

->test('Serialization',3,function() use ($suite){
    // Prepare tests
    $chernozem=new Chernozem;
    $chernozem['basic']=function(){};
    $chernozem['service']=function(){return 72;};
    $chernozem->service('service');
    $chernozem->filter('filter',function($value){
        return ucfirst($value);
    });
    $chernozem['filter']='test';
    // (Un)serialize
    $chernozem=unserialize(serialize($chernozem));
    // Tests
    $suite->check('Closure',$chernozem['basic'] instanceof Closure);
    $suite->check('Service',$chernozem['service']==72);
    $suite->check('Filter',$chernozem['filter']=='Test');
})

->test('toArray',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['array']=array();
    $array=$chernozem->toArray();
    $suite->check('Valid array',is_array($array));
    $suite->check('Valid recursive array',is_array($array['array']));
})

->run();
