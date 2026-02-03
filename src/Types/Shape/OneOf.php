<?php

namespace DevCoding\Reflection\Types\Shape;

class OneOf extends ShapeDefinition
{
  public static function pattern(string $type = 'string'): string
  {
    return '#(?<type>'.$type.')<([^>]+)>#';
  }

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if (!empty($string))
    {
      $matches = array_merge($matches, str_getcsv($string));

      return true;
    }

    return false;
  }
}