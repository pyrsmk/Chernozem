<?php

class D extends \Chernozem{
    protected $a;
    public function __construct(){
        $this->__container=false;
        $this->__properties=false;
    }
}

class None extends \Lumy\Suite\Cli{

    public function __construct(){
        $this->c=new D(array('test'=>'pwet'));
    }

    protected function testContainer(){
        try{
            $this->c['a']=33;
            $this->check("Can't set an option",false);
        }
        catch(\Exception $e){
            $this->check("Can't set an option",true);
        }
        try{
            $this->c['test']='test';
            $this->check("Can't add a value to the containter",false);
        }
        catch(\Exception $e){
            $this->check("Can't add a value to the containter",true);
        }
        try{
            $this->c['a'];
            $this->check("Can't get an option",false);
        }
        catch(\Exception $e){
            $this->check("Can't get an option",true);
        }
        try{
            $this->c['test'];
            $this->check("Can't get a value from the container",false);
        }
        catch(\Exception $e){
            $this->check("Can't get a value from the container",true);
        }
        return 4;
    }

}
