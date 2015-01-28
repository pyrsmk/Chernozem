<?php

########################################################### Prepare

error_reporting(E_ALL);

require __DIR__.'/../src/Chernozem.php';
require __DIR__.'/vendor/autoload.php';

$minisuite=new MiniSuite('Chernozem');

########################################################### Declare test class

class TestClass extends Chernozem{
    protected $a=72;
    protected $_b=array('72');
    protected $__c=true;
}

########################################################### Base tests

$chernozem=new Chernozem(array('test'=>'test'));

$minisuite->expects('[Base] Instantiate with an array')
		  ->that($chernozem['test'])
		  ->equals('test');
	
$chernozem=new Chernozem(array('test'=>'test'));
$chernozem=new Chernozem($chernozem);

$minisuite->expects('[Base] Instantiate with a traversable object')
		  ->that($chernozem['test'])
		  ->equals('test');

########################################################### Container tests

$chernozem=new TestClass;
$chernozem[]=33;

$minisuite->expects('[Container] Add a value')
		  ->that($chernozem[0])
		  ->equals(33);

$chernozem=new TestClass;
$chernozem[33]=33;

$minisuite->expects('[Container] Set/get with an integer key')
		  ->that($chernozem[33])
		  ->equals(33);

$chernozem=new TestClass;
$chernozem['test']=33;

$minisuite->expects('[Container] Set/get with a string key')
		  ->that($chernozem['test'])
		  ->equals(33);

$chernozem=new TestClass;
$chernozem[$chernozem]=33;

$minisuite->expects('[Container] Set/get with an object key')
		  ->that($chernozem[$chernozem])
		  ->equals(33);

$chernozem=new TestClass(array('test'=>true));

$minisuite->expects('[Container] Required value is set')
		  ->that(isset($chernozem['test']))
		  ->isTheSameAs(true);

$minisuite->expects('[Container] Required value is true')
		  ->that($chernozem['test'])
		  ->isTheSameAs(true);

$chernozem=new TestClass(array('test'=>true));
unset($chernozem['test']);

$minisuite->expects('[Container] Unset a value')
		  ->that(isset($chernozem['test']))
		  ->isTheSameAs(false);

$values=array('chaud','cacao','chocho'=>'chocolat');
$chernozem=new TestClass($values);

$minisuite->expects('[Container] Count values')
		  ->that(count($chernozem))
		  ->equals(3);

$minisuite->expects('[Container] Get all values')
		  ->that($chernozem->toArray())
		  ->isTheSameAs($values);

$vals=array();
foreach($chernozem as $key=>$val){
	$vals[$key]=$val;
}

$minisuite->expects('[Container] Iterate')
		  ->that($vals)
		  ->isTheSameAs($values);

########################################################### Properties tests

$chernozem=new TestClass;

$minisuite->expects('[Properties] Get value')
		  ->that($chernozem['a'])
		  ->equals(72);

$chernozem['a']=33;

$minisuite->expects('[Properties] Set value')
		  ->that($chernozem['a'])
		  ->equals(33);

$minisuite->expects('[Properties] Get value from a limited property')
		  ->that($chernozem['b'])
		  ->isTheSameAs(array('72'));

try{
	$chernozem['b']=33;
	$val=false;
}
catch(Exception $e){
	$val=true;
}

$minisuite->expects('[Properties] Set value to a limited property')
		  ->that($val)
		  ->isTheSameAs(true);

$chernozem=new TestClass(array('a'=>'pwet'));

$minisuite->expects('[Properties] Set with constructor')
		  ->that($chernozem['a'])
		  ->equals('pwet');

try{
	$chernozem=new TestClass(array('b'=>'pwet'));
	$val=false;
}
catch(Exception $e){
	$val=true;
}

$minisuite->expects('[Properties] Set with constructor to a limited property')
		  ->that($val)
		  ->isTheSameAs(true);

$chernozem=new TestClass;
unset($chernozem['a']);

$minisuite->expects('[Properties] Cannot unset a value')
		  ->that($chernozem['a'])
		  ->equals(72);

$chernozem=new TestClass(array('a'=>false,'test'));

$minisuite->expects('[Properties] Get values')
		  ->that($chernozem->toArray())
		  ->isTheSameAs(array('test'));

########################################################### Services tests

$chernozem=new TestClass;
$chernozem['service']=function(){
	return true;
};
$chernozem->service('service');

$minisuite->expects('[Services] Set a service')
		  ->that($chernozem['service'])
		  ->isTheSameAs(true);

$chernozem->unservice('service');

$minisuite->expects('[Services] Set a service')
		  ->that($chernozem['service'])
		  ->isInstanceOf('Closure');