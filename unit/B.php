<?php

class B extends \Chernozem{
    protected $a=72;
    protected $_b=7;
    protected $__c;
    protected $d;
    public function __construct($values=array()){
        $this->__container=false;
        parent::__construct($values);
    }
}

class Properties extends \Lumy\Suite\Cli{

    protected function testConstructor(){
        try{
            $this->c=new B(array('a'=>33));
            $this->check('Built with an array',$this->c['a']==33);
        }
        catch(\Exception $e){
            $this->check('Built with an array: '.$e->getMessage(),false);
        }
        return 1;
    }

    protected function testContainer(){
        // Set
        try{
            $this->c[33]=33;
            $this->check("Can't set an option with an integer key",false);
        }
        catch(\Exception $e){
            $this->check("Can't set an option with an integer key",true);
        }
        try{
            $this->c['']=33;
            $this->check("Can't set an option with an empty key",false);
        }
        catch(\Exception $e){
            $this->check("Can't set an option with an empty key",true);
        }
        try{
            $this->c['']=33;
            $this->check("Can't set an non-existent option",false);
        }
        catch(\Exception $e){
            $this->check("Can't set an non-existent option",true);
        }
        try{
            $this->c['b']=33;
            $this->check("Can't set a locked option",false);
        }
        catch(\Exception $e){
            $this->check("Can't set a locked option",true);
        }
        try{
            $this->c['c']=33;
            $this->check("Can't set __ properties",false);
        }
        catch(\Exception $e){
            $this->check("Can't set __ properties",true);
        }
        try{
            $this->c['c']=array();
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
        // Get
        try{
            $this->c[''];
            $this->check("Can't get an non-existent option",false);
        }
        catch(\Exception $e){
            $this->check("Can't get an non-existent option",true);
        }
        try{
            $this->c['c'];
            $this->check("Can't get __ properties",false);
        }
        catch(\Exception $e){
            $this->check("Can't get __ properties",true);
        }
        try{
            $this->check("Get an option",$this->c['a']==33);
        }
        catch(\Exception $e){
            $this->check("Get an option: ".$e->getMessage(),false);
        }
        try{
            $this->check("Get a locked option",$this->c['b']==7);
        }
        catch(\Exception $e){
            $this->check("Get a locked option: ".$e->getMessage(),false);
        }
        // Isset
        $this->check('Non-locked option is set',isset($this->c['a']));
        $this->check('Locked option is set',isset($this->c['b']));
        return 14;
    }

}
