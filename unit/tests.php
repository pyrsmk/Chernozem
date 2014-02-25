<?php

########################################################### Prepare

error_reporting(E_ALL ^ E_NOTICE);

require __DIR__.'/../src/Chernozem.php';
require __DIR__.'/vendor/autoload.php';

$minisuite=new MiniSuite\Cli('Chernozem');
$minisuite->disableAnsiColors();

########################################################### Declare test class

class TestClass extends Chernozem{
    protected $a=72;
    protected $_b=array('72');
    protected $__c=true;
}

########################################################### Base tests

$minisuite->test('[Base] Instantiate with an array',function(){
	$chernozem=new Chernozem(array('test'=>'test'));
	return $chernozem['test']=='test';
});

$minisuite->test('[Base] Instantiate with a traversable object',function(){
	$chernozem=new Chernozem(array('test'=>'test'));
	$chernozem=new Chernozem($chernozem);
	return $chernozem['test']=='test';
});

########################################################### Container tests

$minisuite->test('[Container] Add a value',function(){
	$chernozem=new TestClass;
	$chernozem[]=33;
	return $chernozem[0]==33;
});

$minisuite->test('[Container] Set/get with an integer key',function(){
	$chernozem=new TestClass;
	$chernozem[33]=33;
	return $chernozem[33]==33;
});

$minisuite->test('[Container] Set/get with a string key',function(){
	$chernozem=new TestClass;
	$chernozem['test']=33;
	return $chernozem['test']==33;
});

$minisuite->test('[Container] Set/get with an object key',function(){
	$chernozem=new TestClass;
	$chernozem[$chernozem]=33;
	return $chernozem[$chernozem]==33;
});

$minisuite->test('[Container] Required value is set',function(){
	$chernozem=new TestClass(array('test'=>true));
	return isset($chernozem['test']);
});

$minisuite->test('[Container] Required value is not set',function(){
	$chernozem=new TestClass(array('test'=>true));
	return !isset($chernozem['test2']);
});

$minisuite->test('[Container] Unset a value',function(){
	$chernozem=new TestClass(array('test'=>true));
	unset($chernozem['test']);
	return !isset($chernozem['test']);
});

$minisuite->test('[Container] Count values',function(){
	$chernozem=new TestClass(array('chaud','cacao','chocho'=>'chocolat'));
	return count($chernozem)==3;
});

$minisuite->test('[Container] Get all values',function(){
	$values=array('chaud','cacao','chocho'=>'chocolat');
	$chernozem=new TestClass($values);
	return $chernozem->toArray()===$values;
});

$minisuite->test('[Container] Iterate',function(){
	$values=array('chaud','cacao','chocho'=>'chocolat');
	$chernozem=new TestClass($values);
	$vals=array();
	foreach($chernozem as $key=>$val){
		$vals[$key]=$val;
	}
	return $vals===$values;
});

########################################################### Properties tests

$minisuite->test('[Properties] Get value',function(){
	$chernozem=new TestClass;
	return $chernozem['a']==72;
});

$minisuite->test('[Properties] Set value',function(){
	$chernozem=new TestClass;
	$chernozem['a']=33;
	return $chernozem['a']==33;
});

$minisuite->test('[Properties] Get value from a limited property',function(){
	$chernozem=new TestClass;
	return $chernozem['b']==array('72');
});

$minisuite->test('[Properties] Set value to a limited property',function(){
	$chernozem=new TestClass;
	try{
		$chernozem['b']=33;
		return false;
	}
	catch(Exception $e){
		return true;
	}
});

$minisuite->test('[Properties] Set with constructor',function(){
	$chernozem=new TestClass(array('a'=>'pwet'));
	return $chernozem['a']=='pwet';
});

$minisuite->test('[Properties] Set with constructor to a limited property',function(){
	try{
		$chernozem=new TestClass(array('b'=>'pwet'));
		return false;
	}
	catch(Exception $e){
		return true;
	}
});

$minisuite->test('[Properties] Required value is set',function(){
	$chernozem=new TestClass;
	return isset($chernozem['a']);
});

$minisuite->test('[Properties] Required value is not set',function(){
	$chernozem=new TestClass;
	return !isset($chernozem['d']);
});

$minisuite->test('[Properties] Required limited value is set',function(){
	$chernozem=new TestClass;
	return isset($chernozem['b']);
});

$minisuite->test('[Properties] Required inaccessible value is not set',function(){
	$chernozem=new TestClass;
	return !isset($chernozem['c']);
});

$minisuite->test('[Properties] Unset a value',function(){
	$chernozem=new TestClass;
	unset($chernozem['a']);
	return $chernozem['a']==72;
});

$minisuite->test('[Properties] Properties have priority',function(){
	$chernozem=new TestClass(array('a'=>false,'test'));
	return $chernozem['a']===false && $chernozem->toArray()===array('test');
});

########################################################### Services tests

$minisuite->test('[Services] Set a service',function(){
	$chernozem=new TestClass;
	$chernozem['service']=function(){
		return true;
	};
	$chernozem->service('service');
	return $chernozem['service']===true;
});

$minisuite->test('[Services] Unset a service',function(){
	$chernozem=new TestClass;
	$chernozem['service']=function(){
		return true;
	};
	$chernozem->service('service');
	$chernozem->unservice('service');
	return $chernozem['service'] instanceof Closure;
});

########################################################### Run tests

$minisuite->run();

/*
        // Unset
        unset($this->c['test']);
        $this->check('Unset',!isset($this->c['test']));
        return 15;
    }

    protected function testVarious(){
        $this->c=new C;
        // Not traversable
        try{
            foreach($this->c as $key=>$value){}
            $this->check("Not traversable",false);
        }
        catch(\Exception $e){
            $this->check("Not traversable",true);
        }
        // Countable
        $this->check('0 item in the container',count($this->c)==0);
        // To array
        $this->check('toArray() returns an array',is_array($this->c->toArray()));
        return 3;
    }*/