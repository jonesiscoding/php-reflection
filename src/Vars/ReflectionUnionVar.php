<?php

namespace DevCoding\Reflection\Vars;

use DevCoding\Reflection\RelfectionVar;

/**
 * Reflection-style class similar to ReflectionUnionType. Primarily uses information from PHPdoc tags to allow for
 * support for PHP 7.0 and libraries written for PHP 7.0 which may not have typed properites, parameters, or methods.
 *
 * Instantiation typically happens via the static methods in ReflectionVar, allowing creation from a ReflectionProperty,
 * ReflectionFunction, or magic methods or properties.
 *
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 * @author  AMJones <am@jonesiscoding.com>
 */
class ReflectionUnionVar extends ReflectionVar
{
  /** @var RelfectionVar[] */
  protected $types = [];

  /**
   * @param \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass $reflector   Source of Type
   * @param string[]                                                         $types       Array of individual types
   * @param bool                                                             $allowsNull  If type is nullable
   * @param string                                                           $description Description from PHPdoc
   */
  public function __construct(\Reflector $reflector, array $types, bool $allowsNull, $description = null)
  {
    foreach($types as $type)
    {
      if(ReflectionPrototypeVar::handles($type))
      {
        $this->types[] = new ReflectionPrototypeVar($reflector, $type, $allowsNull, $description);
      }
      else
      {
        $this->types[] = new ReflectionNamedVar($reflector, $type, $allowsNull, $description);
      }
    }

    parent::__construct($reflector, $allowsNull, $description);
  }

  public static function handles($type): bool
  {
    return is_array($type) && count($type) > 1;
  }

  /**
   * @return ReflectionNamedVar[]
   */
  public function getTypes(): array
  {
    return $this->types;
  }

  /**
   * Returns a pipe-delimited string containing each type
   *
   * @return string
   */
  public function __toString()
  {
    $types = $this->types;
    if ($this->allowsNull() && !in_array('null', $types))
    {
      $types[] = 'null';
    }

    return implode('|', $types);
  }
}
