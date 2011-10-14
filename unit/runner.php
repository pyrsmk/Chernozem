<?php

########################################################### Init

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

require __DIR__.'/../lib/Suite.php';
require __DIR__.'/../lib/Suite/Cli.php';

require __DIR__.'/../src/Container.php';
require __DIR__.'/../src/Properties.php';

########################################################### Data

class A extends Chernozem\Container{}

class B extends Chernozem\Properties{
    protected $a=72;
    protected $_b;
    protected $__c;
    protected $d;
}

########################################################### Chernozem\Container

class Container extends Lumy\Suite\Cli{
    
    public function __construct(){
        $this->c=new A;
    }
    
    protected function testtoArray(){
        $this->check('An array was returned',is_array($this->c->toArray()));
        return 1;
    }
    
    protected function testArrayAccess(){
        // Set
        try{
            $this->c[33]=33;
            $this->check('Set with an integer key',true);
        }
        catch(Exception $e){
            $this->check('Set with an integer key: '.$e->getMessage(),false);
        }
        try{
            $this->c[]='test2';
            $this->check('Set with no key',true);
        }
        catch(Exception $e){
            $this->check('Set with no key: '.$e->getMessage(),false);
        }
        try{
            $this->c['test']=33;
            $this->check('Set with a string key',true);
        }
        catch(Exception $e){
            $this->check('Set with a string key: '.$e->getMessage(),false);
        }
        try{
            $this->c['']=33;
            $this->check("Can't set with an empty string key",false);
        }
        catch(Exception $e){
            $this->check("Can't set with an empty string key",true);
        }
        try{
            $this->c[$this->c]='bar';
            $this->check('Set with an object key',true);
        }
        catch(Exception $e){
            $this->check('Set with an object key: '.$e->getMessage(),false);
        }
        // Get
        try{
            $this->check('Get with an integer key',$this->c[33]==33);
        }
        catch(Exception $e){
            $this->check('Get with an integer key: '.$e->getMessage(),false);
        }
        try{
            $this->check('Get with a string key',$this->c['test']==33);
        }
        catch(Exception $e){
            $this->check('Get with a string key: '.$e->getMessage(),false);
        }
        try{
            $this->c[''];
            $this->check("Can't get with an empty string key",false);
        }
        catch(Exception $e){
            $this->check("Can't get with an empty string key",true);
        }
        try{
            $this->check('Get with an object key',$this->c[$this->c]=='bar');
        }
        catch(Exception $e){
            $this->check('Get with an object key: '.$e->getMessage(),false);
        }
        // Isset
        $this->check('Isset',isset($this->c['test']));
        // Unset
        unset($this->c['test']);
        $this->check('Unset',!isset($this->c['test']));
        return 11;
    }
    
    protected function testConstructor(){
        try{
            $this->c=new A(array('test'=>'test'));
            $this->check('Built with an array',$this->c['test']=='test');
        }
        catch(Exception $e){
            $this->check('Built with an array: '.$e->getMessage(),false);
        }
        try{        
            $this->c=new A($this->c);
            $this->check('Built with a traversable object',$this->c['test']=='test');
        }
        catch(Exception $e){
            $this->check('Built with a traversable object: '.$e->getMessage(),false);
        }
        return 2;
    }
    
    protected function testIterator(){
        unset($this->c['test']);
        $this->c['test1']=1;
        $this->c['test2']=2;
        $this->c['test3']=3;
        $this->c['test4']=4;
        $this->c['test5']=5;
        $i=0;
        foreach($this->c as $key=>$value){
            $this->check('Valid key '.(++$i),$key=="test$i");
            $this->check('Valid value '.$i,$value==$i);
        }
        return 10;
    }
    
    protected function testCountable(){
        $this->check('5 items in the container',count($this->c)==5);
        return 1;
    }
    
}

$suite=new Container;
$suite->run();

########################################################### Chernozem\Properties

class Properties extends Lumy\Suite\Cli{
    
    public function __construct(){
        $this->c=new B;
    }
    
    protected function testArrayAccess(){
        // Set
        try{
            $this->c[33]=33;
            $this->check("Can't set an option with an integer name",false);
        }
        catch(Exception $e){
            $this->check("Can't set an option with an integer name",true);
        }
        try{
            $this->c['']=33;
            $this->check("Can't set an option with an empty name",false);
        }
        catch(Exception $e){
            $this->check("Can't set an option with an empty name",true);
        }
        try{
            $this->c['']=33;
            $this->check("Can't set an non-existent option",false);
        }
        catch(Exception $e){
            $this->check("Can't set an non-existent option",true);
        }
        try{
            $this->c['b']=33;
            $this->check("Can't set a locked option",false);
        }
        catch(Exception $e){
            $this->check("Can't set a locked option",true);
        }
        try{
            $this->c['c']=33;
            $this->check("Can't set __ properties",false);
        }
        catch(Exception $e){
            $this->check("Can't set __ properties",true);
        }
        try{
            $this->c['c']=array();
            $this->check("Can't set an option from another type",false);
        }
        catch(Exception $e){
            $this->check("Can't set an option from another type",true);
        }
        try{
            $this->c['a']=33;
            $this->check("Set an option",true);
        }
        catch(Exception $e){
            $this->check("Set an option: ".$e->getMessage(),false);
        }
        try{
            $this->c['d']=33;
            $this->check("Set an non-yet-defined option",true);
        }
        catch(Exception $e){
            $this->check("Set an non-yet-defined option: ".$e->getMessage(),false);
        }
        // Get
        try{
            $this->c[''];
            $this->check("Can't get an non-existent option",false);
        }
        catch(Exception $e){
            $this->check("Can't get an non-existent option",true);
        }
        try{
            $this->c['c'];
            $this->check("Can't get __ properties",false);
        }
        catch(Exception $e){
            $this->check("Can't get __ properties",true);
        }
        try{
            $this->check("Get an option",$this->c['a']==33);
        }
        catch(Exception $e){
            $this->check("Get an option: ".$e->getMessage(),false);
        }
        // Isset
        $this->check('Non-locked option is set',isset($this->c['a']));
        $this->check('Locked option is set',isset($this->c['b']));
        return 13;
    }
    
    protected function testConstructor(){
        try{
            $this->c=new B(array('a'=>10));
            $this->check('Built with an array',$this->c['a']==10);
        }
        catch(Exception $e){
            $this->check('Built with an array: '.$e->getMessage(),false);
        }
        return 1;
    }
    
}

$suite=new Properties;
$suite->run();
