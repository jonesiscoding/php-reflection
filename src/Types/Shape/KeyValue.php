<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Type;


class KeyValue extends ShapeDefinition
{
  public static function pattern(string $type = 'array'): string
  {
    return '#(?<type>' . $type . ')<(?<inner>([^>]+)>$#';
  }

  /**
   * @param string          $string   Shape String (key: type, key: type)
   * @param \Reflector|null $context  Source of Type
   * @param array           $matches  Shape Array; Set by Reference
   * @return bool                     TRUE if given string matches this shape
   * @throws \ReflectionException     If one of the types in the shape cannot be resolved
   */
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if (!empty($string))
    {
      $parts = str_getcsv($string);
      if (count($parts) > 1)
      {
        $matches['key'] = Type::from(array_shift($parts), $context);
      }

      $matches['value'] = Type::from(array_shift($parts), $context);

      return true;
    }

    return false;
  }
}
