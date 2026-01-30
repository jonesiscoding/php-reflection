<?php

namespace DevCoding\Reflection\Exceptions;

use Psr\Container\NotFoundExceptionInterface;

class ReflectorNotFoundException extends \ReflectionException implements NotFoundExceptionInterface
{

}
