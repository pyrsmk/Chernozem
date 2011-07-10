<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require(__DIR__.'/Suite.php');
require(__DIR__.'/Cli.php');

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
    $chernozem['test']=function(){return 33;};
    $suite->check('Closures',$chernozem['test']==33);
})

->test('Locked value',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){return 33;};
    $chernozem->lock('test');
    try{
        $chernozem['test']=72;
        $suite->check('Cannot set',true);
    }
    catch(Exception $e){
        $suite->check('Cannot set',false);
    }
    try{
        unset($chernozem['test']);
        $suite->check('Cannot unset',true);
    }
    catch(Exception $e){
        $suite->check('Cannot unset',false);
    }
})

->test('Type-hinting',16,function() use ($suite){
    $chernozem=new Chernozem;
    $types=array(
        'int'       => 33,
        'integer'   => 33,
        'long'      => 33,
        'float'     => 5.5,
        'double'    => 5.5,
        'real'      => 5.5,
        'numeric'   => '72',
        'bool'      => true,
        'boolean'   => false,
        'string'    => 'pouet!',
        'scalar'    => 72,
        'array'     => array(72),
        'object'    => new stdClass,
        'resource'  => curl_init(),
        'callable'  => function(){}
    );
    foreach($types as $type=>$value){
        $chernozem->hint('test',array($type));
        try{
            $chernozem['test']=$value;
            $suite->check("Of type $type",true);
        }
        catch(Exception $e){
            $suite->check("Of type $type",false);
        }
    }
    $chernozem->hint('test',array('Chernozem'));
    try{
        $chernozem['test']=$chernozem;
        $suite->check("Instance of Chernozem",true);
    }
    catch(Exception $e){
        $suite->check("Instance of Chernozem",false);
    }
})

->test('Persistent value',1,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){return microtime();};
    $chernozem->persist('test');
    $suite->check('Same microtime value',$chernozem['test']==$chernozem['test']);
})

->test('Integrated value',1,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){};
    $chernozem->integrate('test');
    $suite->check('Is a closure',$chernozem['test'] instanceof Closure);
})

->test('Setter/getter',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){};
    $chernozem->integrate('test');
    $chernozem->setter('test',function($value){return $value;});
    $suite->check('Is a closure',$chernozem['test'] instanceof Closure);
    $chernozem->getter('test',function($value){return 72;});
    $suite->check('Is equal to 72',$chernozem['test']==72);
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

->test('Serialization',4,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test1']=function(){return 33;};
    $chernozem['test2']=function(){return 33;};
    $chernozem->lock('test2');
    $chernozem['test3']=$chernozem->persist(function(){return microtime();});
    $chernozem['test4']=$chernozem->integrate(function(){});
    $chernozem=unserialize(serialize($chernozem));
    $suite->check('Closure',$chernozem['test1']==33);
    $suite->check('Locked',$chernozem['test2']==33);
    $microtime=$chernozem['test3'];
    $suite->check('Persistent',$chernozem['test3']==$microtime);
    $suite->check('Integrated',$chernozem['test4'] instanceof Closure);
})

->run();
