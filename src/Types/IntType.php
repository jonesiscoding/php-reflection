<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Shape\MinMax;
use DevCoding\Reflection\Types\Shape\ShapeTrait;

class IntType extends Builtin implements ShapeInterface
{
  use ShapeTrait;

  const SHAPES = [MinMax::class];

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return static::matchShape($string, $context, $matches) || self::INT === $string;
  }
}
