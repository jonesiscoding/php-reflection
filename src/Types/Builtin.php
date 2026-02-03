<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Factory\Match;

class Builtin extends Type
{
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return !str_contains($string, '\\') && array_key_exists($string, static::getBuiltin());
  }
}
