<?php

namespace Chernozem;

use Interop\Container\Exception\ContainerException as InteropContainerException;

class ContainerException extends \Exception implements InteropContainerException {}