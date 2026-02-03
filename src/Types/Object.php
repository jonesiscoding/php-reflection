<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\ReflectionContext;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Factory\Match;

class Object extends Type implements TypeInterface
{
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if (str_contains($string, '\\') || !array_key_exists($string, static::getBuiltin()))
    {
      if (class_exists($string) || interface_exists($string) || trait_exists($string))
      {
        $matches['type'] = $string;
      }
      elseif ($resolved = ReflectionContext::from($context)->tryResolve($string))
      {
        $matches['type'] = $resolved;
      }
    }

    return isset($matches['type']);
  }
}
