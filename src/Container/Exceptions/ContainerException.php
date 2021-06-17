<?php

namespace Symfox\Container\Exceptions;

use Psr\Container\ContainerExceptionInterface;
use Exception;

/**
 * Class could not be instantiated
 */
class ContainerException extends Exception implements ContainerExceptionInterface {}