<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\Compound;
use DevCoding\Reflection\Types\Factory\Match;

class Prototype extends Compound
{
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return preg_match('#^(array\|)?(?<inner>[a-zA-Z0-9\\]+\[])(\|array)?$#', $string, $matches);
  }

  public function isPrototype(): bool
  {
    return true;
  }
}
