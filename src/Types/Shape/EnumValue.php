<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Type;

class EnumValue extends ShapeDefinition
{
  use EnumTrait;

  public static function pattern(string $type = null): string
  {
    return '#value-of<(?<inner>[^>,]+)>#';
  }

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if ($enum = static::getEnum($string))
    {
      if (!$type = static::getTypeFromCases($string))
      {
        $ref   = new \ReflectionClass($enum);
        $vals  = array_values($ref->getConstants());
        $type  = gettype(array_pop($vals));
        $other = array_filter($vals, function($v) use ($type) { return gettype($v) !== $type; });

        if (!empty($other))
        {
          throw new \ReflectionException(
            'The class '.$string.' cannot be used as an Enum; constant value types do not match.'
          );
        }
      }

      $matches = ['type' => Type::from($type, new \ReflectionClass($enum))];

      return true;
    }

    throw new \ReflectionException(sprintf(
      'The %s %s indicated in PHPdoc @value-of tag is not valid',
      $string,
      function_exists('enum_exists') ? 'enum' : 'class'
    ));
  }
}
