<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Nullable;

/**
 * @method Type offsetGet($offset)
 * @method Type __get($name)
 * @method \ArrayIterator|Type[] getIterator()
 */
class Map extends ShapeDefinition
{
  public static function pattern(string $type = 'array|object'): string
  {
    return '#(?<type>'.$type.'){(?<inner>[^}]+)}$#';
  }

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if (preg_match_all('#(?<key>\w+)(?<optional>\?)?\s*:\s*(?<type>[^,]+)#', $string, $m, PREG_SET_ORDER))
    {
      foreach($m as $set)
      {
        $key = $set['key'];
        if ($set['optional'])
        {
          $value = Nullable::from($set['type'], $context);
        }
        else
        {
          $value = Type::from($set['type'], $context);
        }

        $matches[$key] = $value;
      }

      return true;
    }

    return false;
  }
}
