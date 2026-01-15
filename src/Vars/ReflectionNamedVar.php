<?php

namespace DevCoding\Reflection\Vars;

use DevCoding\Reflection\ReflectionClassImports;

/**
 * Reflection-style class similar to ReflectionNamedType, but primarily using information from PHPdoc tags to allow for
 * support for PHP 7.0 and libraries written for PHP 7.0 which may not have typed properites, parameters, or methods.
 *
 * Instantiation typically happens via the static methods in ReflectionVar, allowing creation from a ReflectionProperty,
 * ReflectionFunction, or magic methods or properties.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionNamedVar extends ReflectionVar
{
  const MIXED = 'mixed';

  /** @var string */
  protected $type = self::MIXED;
  /** @var bool */
  protected $resolved = false;

  /**
   * @param \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass $reflector     Source of Type
   * @param string                                                           $type          Pipe separate string
   * @param bool                                                             $allowsNull    If type is nullable
   * @param string                                                           $description   Description from PHPdoc
   */
  public function __construct(\Reflector $reflector, string $type = self::MIXED, bool $allowsNull = false, string $description = '')
  {
    $this->type = !empty($type) ? $type : $this->type;

    parent::__construct($reflector, $allowsNull, $description);
  }

  public static function handles($type): bool
  {
    $type = is_array($type) && count($type) === 1 ? reset($type) : $type;

    return is_string($type) && !ReflectionPrototypeVar::handles($type) && !ReflectionEnumVar::handles($type);
  }

  /**
   * @return bool
   */
  public function isBuiltin(): bool
  {
    return 'mixed' === $this->type || function_exists('is_'.$this->type);
  }

  /**
   * @return bool
   */
  public function isBool(): bool
  {
    return 'bool' === $this->type;
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    if (!$this->resolved)
    {
      $this->resolved = true;
      if (!$this->isBuiltin() && !class_exists($this->type))
      {
        $class = $this->getDeclaringClass();
        if (class_exists($class->getNamespaceName() . '\\' . $this->type))
        {
          $this->type = $class->getNamespaceName() . '\\' . $this->type;
        }
        else
        {
          $imports = new ReflectionClassImports($class);
          if ($imports->offsetExists($this->type))
          {
            $this->type = $imports->offsetGet($this->type);
          }
        }
      }
    }

    return $this->type;
  }

  /**
   * @return ReflectionPrototypeVar
   */
  public function toPrototype(): ReflectionPrototypeVar
  {
    return new ReflectionPrototypeVar($this->reflector, $this->getName(), $this->allowsNull, $this->description);
  }

  /**
   * @return string
   */
  public function __toString()
  {
    try
    {
      return $this->getName();
    }
    catch(\Throwable $throwable)
    {
      return $this->type;
    }
  }

  /**
   * @return \ReflectionClass|null
   */
  protected function getDeclaringClass()
  {
    if ($this->reflector instanceof \ReflectionClass)
    {
      return $this->reflector;
    }
    elseif (method_exists($this->reflector, 'getDeclaringClass'))
    {
      return $this->reflector->getDeclaringClass();
    }

    return null;
  }
}
