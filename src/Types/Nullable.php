<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\Compound;
use DevCoding\Reflection\Types\Factory\Factory;

class Nullable extends Compound
{
  // region //////////////////////////////////////////////// Static Handler Methods

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    $pipes = substr_count($string, '|');
    if ($pipes <= 1 && static::NULL !== $string)
    {
      if (false !== strpos($string, 'null'))
      {
        $matches[Factory::TYPE] = trim(str_replace('null', '', $string), '|?');
      }
      elseif (0 === $pipes && str_starts_with($string, '?'))
      {
        $matches[Factory::TYPE] = substr($string, 1);
      }
      elseif (static::isNullableContext($context))
      {
        $matches[Factory::TYPE] = $string;
      }
    }

    return isset($matches[Factory::TYPE]);
  }

  /**
   * @param \Reflector $context
   *
   * @return bool
   */
  protected static function isNullableContext(\Reflector $context): bool
  {
    if($context instanceof \ReflectionParameter)
    {
      return $context->allowsNull();
    }
    elseif ($context instanceof \ReflectionProperty)
    {
      if (method_exists($context, 'hasType') && $context->hasType())
      {
        return method_exists($context, 'getType') && $context->getType()->allowsNull();
      }
    }
    elseif ($context instanceof \ReflectionFunctionAbstract)
    {
      return $context->hasReturnType() && $context->getReturnType()->allowsNull();
    }

    return false;
  }

  // endregion ///////////////////////////////////////////// End Static Handler Methods

  // region //////////////////////////////////////////////// Other Methods

  public function allowsNull(): bool
  {
    return true;
  }

  // endregion ///////////////////////////////////////////// End Other Methods
}
