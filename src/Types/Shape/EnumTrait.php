<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\ReflectionContext;
use DevCoding\Reflection\Types\Type;

/**
 * @property Type $type
 * @method Type offsetGet($offset)
 */
trait EnumTrait
{
  /**
   * @return Type[]|\ArrayIterator
   */
  abstract public function getIterator();

  /**
   * @return Type
   */
  public function getType()
  {
    return $this->getIterator()->offsetGet('type');
  }

  /**
   * @param string          $string
   * @param \Reflector|null $context
   *
   * @return string|null
   */
  protected static function getEnum(string $string, \Reflector $context = null)
  {
    $class = class_exists($string) ? $string : ReflectionContext::from($context)->resolve($context);

    if (!function_exists('enum_exists'))
    {
      return $class;
    }
    else
    {
      return !empty($class) && enum_exists($class) ? $class : null;
    }
  }

  protected static function getTypeFromCases(string $string)
  {
    if (function_exists('enum_exists') && enum_exists($string))
    {
      if (class_exists('\BackedEnum') && !is_a($string, \BackedEnum::class, true))
      {
        return $string;
      }

      $cases = $string::cases();
    }
    elseif (is_callable([$string, 'cases']))
    {
      $cases = $string::cases();
    }

    return isset($cases) ? gettype(reset($cases)) : null;
  }
}
