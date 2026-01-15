<?php

namespace DevCoding\Reflection\Vars;

use DevCoding\Reflection\Comments\ReflectionClassComment;
use DevCoding\Reflection\Comments\ReflectionFunctionComment;
use DevCoding\Reflection\Comments\ReflectionPropertyComment;
use DevCoding\Reflection\Exceptions\TagNotFoundException;

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
abstract class ReflectionVar
{
  /** @var bool */
  protected $allowsNull;
  /** @var \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionParameter|\ReflectionClass */
  protected $reflector;
  /** @var string|null */
  protected $description;

  /**
   * @param \Reflector  $reflector
   * @param bool        $allowsNull
   * @param string|null $description
   */
  protected function __construct(\Reflector $reflector, $allowsNull = false, $description = null)
  {
    $this->reflector   = $reflector;
    $this->description = $description;
    $this->allowsNull  = $allowsNull;
  }

  /**
   * @param \ReflectionClass $class
   * @param string           $name
   *
   * @return ReflectionVar
   * @throws TagNotFoundException
   */
  public static function fromMagicMethod(\ReflectionClass $class, string $name): ReflectionVar
  {
    return (new ReflectionClassComment($class))->getMethod($name)->type;
  }

  /**
   * @param \ReflectionClass $class
   * @param string           $methodName
   * @param string           $paramName
   *
   * @return ReflectionVar
   * @throws TagNotFoundException
   */
  public static function fromMagicMethodParameter(\ReflectionClass $class, string $methodName, string $paramName): ReflectionVar
  {
    return (new ReflectionClassComment($class))->getMethod($methodName)->getParam($paramName)->type;
  }

  /**
   * @param \ReflectionClass $class
   * @param string           $name
   *
   * @return ReflectionVar
   * @throws TagNotFoundException
   */
  public static function fromMagicProperty(\ReflectionClass $class, string $name): ReflectionVar
  {
    return (new ReflectionClassComment($class))->getProperty($name)->type;
  }

  /**
   * @param \ReflectionMethod $ReflectionMethod
   *
   * @return ReflectionVar
   */
  public static function fromReflectionMethod(\ReflectionMethod $ReflectionMethod): ReflectionVar
  {
    return (new ReflectionFunctionComment($ReflectionMethod))->getReturnType();
  }

  /**
   * @param \ReflectionParameter $ReflectionParameter
   *
   * @return ReflectionVar
   */
  public static function fromReflectionParameter(\ReflectionParameter $ReflectionParameter): ReflectionVar
  {
    $RFunc = $ReflectionParameter->getDeclaringFunction();
    $name  = $ReflectionParameter->getName();

    return (new ReflectionFunctionComment($RFunc))->getParam($name)->type;
  }

  /**
   * @param \ReflectionProperty $property
   *
   * @return ReflectionVar
   */
  public static function fromReflectionProperty(\ReflectionProperty $property): ReflectionVar
  {
    return (new ReflectionPropertyComment($property))->getType();
  }

  /**
   * @return bool
   */
  public function allowsNull(): bool
  {
    return $this->allowsNull;
  }

  /**
   * @return string
   */
  abstract public function __toString();
}
