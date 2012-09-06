<?php

class A extends \Chernozem{
    public function __construct($values=array()){
        $this->__properties=false;
        parent::__construct($values);
    }
}

class Container extends \Lumy\Suite\Cli{

    protected function testConstructor(){
        try{
            $this->c=new A(array('test'=>'test'));
            $this->check('Built with an array',$this->c['test']=='test');
        }
        catch(\Exception $e){
            $this->check('Built with an array: '.$e->getMessage(),false);
        }
        try{        
            $this->c=new A($this->c);
            $this->check('Built with a traversable object',$this->c['test']=='test');
        }
        catch(\Exception $e){
            $this->check('Built with a traversable object: '.$e->getMessage(),false);
        }
        return 2;
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
            $this->c['']=33;
            $this->check("Can't set with an empty string key",false);
        }
        catch(\Exception $e){
            $this->check("Can't set with an empty string key",true);
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
            $this->c[''];
            $this->check("Can't get with an empty string key",false);
        }
        catch(\Exception $e){
            $this->check("Can't get with an empty string key",true);
        }
        try{
            $this->check('Get with an object key',$this->c[$this->c]=='bar');
        }
        catch(\Exception $e){
            $this->check('Get with an object key: '.$e->getMessage(),false);
        }
        // Isset
        $this->check('Isset',isset($this->c['test']));
        // Unset
        unset($this->c['test']);
        $this->check('Unset',!isset($this->c['test']));
        return 11;
    }

    protected function testVarious(){
        $this->c=new A;
        // Traversable
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
        // Countable
        $this->check('5 items in the container',count($this->c)==5);
        // To array
        $this->check('toArray() returns an array',is_array($this->c->toArray()));
        return 12;
    }

}
