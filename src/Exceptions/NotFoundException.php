<?php

namespace DevCoding\Reflection\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \InvalidArgumentException implements NotFoundExceptionInterface
{

}