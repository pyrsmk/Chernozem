<?php

########################################################### Init

error_reporting(E_ALL ^ E_NOTICE);

require __DIR__.'/../lib/Suite.php';
require __DIR__.'/../lib/Suite/Cli.php';

require __DIR__.'/../src/Chernozem.php';

require __DIR__.'/A.php';
require __DIR__.'/B.php';
require __DIR__.'/C.php';
require __DIR__.'/D.php';

########################################################### Run tests

$suite=new Container;
$suite->run();

$suite=new Properties;
$suite->run();

$suite=new Container_and_properties;
$suite->run();

$suite=new None;
$suite->run();
