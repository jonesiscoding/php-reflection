<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Vars\ReflectionNamedVar;
use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection-style class for manipulation of a construct representing class, property, parameter, or function name.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionConstruct
{
  const GET = 'get';
  const SET = 'set';
  const IS  = 'is';
  const HAS = 'has';

  const PATTERN_PASCAL = ' _-';
  const PATTERN_SNAKE  = '~(?<=\\w)([A-Z])~';

  /** @var \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass|\ReflectionParameter|ReflectionString */
  protected $reflector;
  /** @var string */
  protected $name;

  public function __construct(\Reflector $reflector)
  {
    $this->reflector = $reflector;
    $this->name      = method_exists($reflector, 'getName') ? $reflector->getName() : (string) $reflector;
  }

  /**
   * Converts this object into a camelCase string  (IE - Converts 'class_name' to 'className')
   *
   * @return string
   */
  public function camel(): string
  {
    return lcfirst($this->pascal());
  }

  /**
   * Returns the expected setter method name, if this object's reflector is a ReflectionProperty
   *
   * @param string $prefix
   * @param bool   $normalize
   *
   * @return string
   */
  public function method($prefix = self::GET, $normalize = true): string
  {
    if ($this->reflector instanceof \ReflectionProperty)
    {
      if (self::GET === $prefix && $normalize)
      {
        $var = ReflectionVar::fromReflectionProperty($this->reflector);
        if ($var instanceof ReflectionNamedVar && $var->isBool())
        {
          $prefix = self::IS;
        }
      }

      if (self::SET === $prefix || self::HAS === $prefix || self::IS === $prefix || self::GET === $prefix)
      {
        return $prefix . $this->pascal();
      }

      throw new \InvalidArgumentException(("Invalid method prefix '$prefix' for given."));
    }

    throw new \LogicException('%s::method can only be used for ' . $this->name . '.');
  }

  /**
   * Converts this object into a PascalCase string. IE- Converts 'class_name' to 'ClassName'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector classify)
   * @return string The PascalCase string
   */
  public function pascal(): string
  {
    return str_replace(str_split(static::PATTERN_PASCAL), '', $this->ucwords());
  }

  /**
   * Converts this object name into a snake_case string. IE- Converts 'ClassName' to 'class_name'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector tableize)
   * @return string
   */
  public function snake(): string
  {
    return strtolower(preg_replace(static::PATTERN_SNAKE, '_$1', $this->name));
  }

  /**
   * Converts this object to a string with capitalized words, separated by the given separators
   *
   * @param string $separators
   *
   * @return string
   */
  public function ucwords($separators = self::PATTERN_PASCAL): string
  {
    return ucwords($this->name, $separators);
  }
}
