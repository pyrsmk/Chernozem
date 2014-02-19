<?php

class C extends \Chernozem{
    protected $a=72;
    protected $_b=7;
    protected $__c;
    protected $d;
    public function __construct($values=array()){
        $this->__traversable=false;
        $this->__nullable=true;
        parent::__construct($values);
    }
}

class Container_and_properties extends \Lumy\Suite\Cli{

    public function __construct(){
        $this->c=new C;
    }

    protected function testContainer(){
        // Set
        try{
            $this->c[33]=33;
            $this->check('Set with an integer key',true);
        }
        catch(\Exception $e){
            $this->check('Set with an integer key: '.$e->getMessage(),false);
        }
        try{
            $this->c[]='test2';
            $this->check('Set with no key',true);
        }
        catch(\Exception $e){
            $this->check('Set with no key: '.$e->getMessage(),false);
        }
        try{
            $this->c['test']=33;
            $this->check('Set with a string key',true);
        }
        catch(\Exception $e){
            $this->check('Set with a string key: '.$e->getMessage(),false);
        }
        try{
            $this->c[$this->c]='bar';
            $this->check('Set with an object key',true);
        }
        catch(\Exception $e){
            $this->check('Set with an object key: '.$e->getMessage(),false);
        }
        // Get
        try{
            $this->check('Get with an integer key',$this->c[33]==33);
        }
        catch(\Exception $e){
            $this->check('Get with an integer key: '.$e->getMessage(),false);
        }
        try{
            $this->check('Get with a string key',$this->c['test']==33);
        }
        catch(\Exception $e){
            $this->check('Get with a string key: '.$e->getMessage(),false);
        }
        try{
            $this->check('Get with an object key',$this->c[$this->c]=='bar');
        }
        catch(\Exception $e){
            $this->check('Get with an object key: '.$e->getMessage(),false);
        }
        try{
            $this->c['b']=33;
            $this->check("Can't set a locked option",false);
        }
        catch(\Exception $e){
            $this->check("Can't set a locked option",true);
        }
        try{
            $this->c['b']=array();
            $this->check("Can't set an option from another type",false);
        }
        catch(\Exception $e){
            $this->check("Can't set an option from another type",true);
        }
        try{
            $this->c['a']=33;
            $this->check("Set an option",true);
        }
        catch(\Exception $e){
            $this->check("Set an option: ".$e->getMessage(),false);
        }
        try{
            $this->c['d']=33;
            $this->check("Set an non-yet-defined option",true);
        }
        catch(\Exception $e){
            $this->check("Set an non-yet-defined option: ".$e->getMessage(),false);
        }
        // Isset
        $this->check('Container is set',isset($this->c['test']));
        $this->check('Non-locked option is set',isset($this->c['a']));
        $this->check('Locked option is set',isset($this->c['b']));
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
    }

}
