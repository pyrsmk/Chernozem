<?php

namespace Chernozem;

use Interop\Container\ContainerInterface;

/*
	Service provider interface
*/
interface ServiceProviderInterface {
	
	/*
		Register service
		
		Parameters
			Interop\Container\ContainerInterface $container
	*/
	public function register(ContainerInterface $container);
	
}