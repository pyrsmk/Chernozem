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
    $chernozem['test']=function(){return 33;};
    $suite->check('Closures',$chernozem['test']==33);
})

->test('Locked value',2,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test']=function(){return 33;};
    $chernozem->lock('test');
    try{
        $chernozem['test']=72;
        $suite->check('Cannot set',false);
    }
    catch(Exception $e){
        $suite->check('Cannot set',true);
    }
    try{
        unset($chernozem['test']);
        $suite->check('Cannot unset',false);
    }
    catch(Exception $e){
        $suite->check('Cannot unset',true);
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

->test('Serialization',7,function() use ($suite){
    $chernozem=new Chernozem;
    $chernozem['test1']=function(){return 72;};
    $chernozem['test2']=72;
    $chernozem->lock('test2');
    $chernozem['test3']=72;
    $chernozem->hint('test3','int');
    $chernozem['test4']=function(){return microtime();};
    $chernozem->persist('test4');
    $chernozem['test5']=function(){};
    $chernozem->integrate('test5');
    $chernozem->setter('test6',function($value){
        return ucfirst($value);
    });
    $chernozem['test7']='test7';
    $chernozem->getter('test7',function($value){
        return ucfirst($value);
    });
    $chernozem=unserialize(serialize($chernozem));
    $suite->check('Closure',$chernozem['test1']==72);
    try{
        $chernozem['test2']=72;
        $suite->check('Locked',false);
    }
    catch(Exception $e){
        $suite->check('Locked',true);
    }
    try{
        $chernozem['test3']='foobar';
        $suite->check('Type-hinted',false);
    }
    catch(Exception $e){
        $suite->check('Type-hinted',true);
    }
    $microtime=$chernozem['test4'];
    $suite->check('Persistent',$chernozem['test4']==$microtime);
    $suite->check('Integrated',$chernozem['test5'] instanceof Closure);
    $chernozem['test6']='test6';
    $suite->check('Setter',$chernozem['test6']=='Test6');
    $suite->check('Getter',$chernozem['test7']=='Test7');
})

->run();
