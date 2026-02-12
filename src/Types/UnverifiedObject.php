<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\ReflectionContext;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Factory\Match;

class UnverifiedObject extends Object
{
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    try
    {
      $result = parent::match($string, $context, $matches);
    }
    catch(\Throwable $t)
    {
      $result = false;
    }

    if (!$result && str_contains($string, '\\') || !array_key_exists($string, static::getBuiltin()))
    {
      $matches['type'] = $string;
    }

    return isset($matches['type']);
  }
}
