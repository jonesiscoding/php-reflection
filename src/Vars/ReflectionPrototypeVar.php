<?php

namespace DevCoding\Reflection\Vars;

/**
 * Extends ReflectionNamedVar to represent an array of the named type.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionPrototypeVar extends ReflectionNamedVar
{
  public function __construct(\Reflector $reflector, string $type = self::MIXED, bool $allowsNull = false, string $description = '')
  {
    if ('[]' === substr($type, -2))
    {
      $type = substr($type, 0, -2);
    }

    parent::__construct($reflector, $type, $allowsNull, $description);
  }

  public static function handles($type): bool
  {
    $type = is_array($type) && count($type) === 1 ? reset($type) : $type;

    return is_string($type) && '[]' === substr($type, -2);
  }

  /**
   * @return string Type followed by [] to indicate an array of that type
   */
  public function __toString()
  {
    try
    {
      return $this->getName() . '[]';
    }
    catch(\Throwable $e)
    {
      return $this->type . '[]';
    }
  }

  /**
   * @param ReflectionNamedVar $var
   *
   * @return ReflectionPrototypeVar
   */
  public static function fromReflectionNamedVar(ReflectionNamedVar $var): ReflectionPrototypeVar
  {
    return new ReflectionPrototypeVar($var->reflector, $var->getName(), $var->allowsNull(), $var->description);
  }

  /**
   * @param ReflectionUnionVar $var
   *
   * @return ReflectionUnionVar
   */
  public static function fromReflectionUnionVar(ReflectionUnionVar $var): ReflectionUnionVar
  {
    $proto = array();
    foreach($var->getTypes() as $ReflectionNamedVar)
    {
      $proto[] = $ReflectionNamedVar->getName() . '[]';
    }

    return new ReflectionUnionVar($var->reflector, $proto, $var->allowsNull(), $var->description);
  }
}
