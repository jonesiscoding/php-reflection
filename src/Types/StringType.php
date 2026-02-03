<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Shape\EnumKey;
use DevCoding\Reflection\Types\Shape\EnumValue;
use DevCoding\Reflection\Types\Shape\OneOf;
use DevCoding\Reflection\Types\Shape\ShapeTrait;

class StringType extends Builtin implements ShapeInterface
{
  const SHAPES = [OneOf::class, EnumKey::class, EnumValue::class];

  use ShapeTrait;

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return static::matchShape($string, $context, $matches) || self::STRING === $string;
  }
}
