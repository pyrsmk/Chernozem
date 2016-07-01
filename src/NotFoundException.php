<?php

namespace Chernozem;

use Interop\Container\Exception\NotFoundException as InteropNotFoundException;

class NotFoundException extends ContainerException implements InteropNotFoundException {}