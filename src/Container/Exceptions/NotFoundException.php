<?php

namespace Symfox\Container\Exceptions;

use Psr\Container\NotFoundExceptionInterface;
use Exception;

/**
 * Class not found
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface {}