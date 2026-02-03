<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Base\VariableInterface;
use DevCoding\Reflection\Types\Shape\KeyValue;
use DevCoding\Reflection\Types\Shape\Map;
use DevCoding\Reflection\Types\Shape\ShapeTrait;

class ArrayType extends Builtin implements ShapeInterface, VariableInterface
{
  const SHAPES = [ Map::class, KeyValue::class ];

  use ShapeTrait;

  public function setType(string $type): VariableInterface
  {
    $this->normalized = $type;

    return $this;
  }

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return static::matchShape($string, $context, $matches) || self::STRING === $string;
  }
}
