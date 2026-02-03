<?php

namespace DevCoding\Reflection\Exceptions;

class WithoutTypeReflectionException extends \ReflectionException
{
  /**
   * @param \Reflector     $reflector
   * @param                $message
   * @param                $code
   * @param Throwable|null $previous
   */
  public function __construct(\Reflector $reflector, $message = "", $code = 0, Throwable $previous = null)
  {
    $tmpl  = '%s cannot be extracted from a %s that does not have a %stype.';
    $third = $reflector instanceof \ReflectionFunctionAbstract ? 'return ' : '';
    $msg   = [sprintf($tmpl, get_called_class(), get_class($reflector), $third), $message];

    parent::__construct(implode(' ', array_filter($msg)), $code, $previous);
  }
}
