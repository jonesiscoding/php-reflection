<?php

namespace DevCoding\Reflection\Exceptions;


use Reflector;

class TagNotFoundException extends NotFoundException
{
  public function __construct(string $tag, $code = 0, Throwable $previous = null)
  {
    parent::__construct(sprintf('@%s not found in document comment', $tag), $code, $previous);
  }

  /**
   * @param Reflector $reflector
   *
   * @return void
   */
  public function setReflector(Reflector $reflector)
  {
    if ($reflector instanceof \ReflectionClass)
    {
      $in = $reflector->getName();
    }
    elseif ($reflector instanceof \ReflectionMethod || $reflector instanceof \ReflectionProperty)
    {
      $in = sprintf('%s::%s()', $reflector->getDeclaringClass(), $reflector->getName());
    }

    if (!empty($in))
    {
      $this->message = sprintf('%s in %s', trim($this->message, ".\n\r\t\v\0"), $in);
    }
  }
}