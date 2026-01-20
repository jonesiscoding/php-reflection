<?php

namespace DevCoding\Reflection\Comments;

/**
 * Reflection-style object representing the DocComment of a ReflectionMethod
 *
 * @property \ReflectionMethod $reflector
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionMethodComment extends ReflectionFunctionComment
{
  /**
   * @return \ReflectionClass
   */
  public function getDeclaringClass(): \ReflectionClass
  {
    return $this->reflector->getDeclaringClass();
  }
}
