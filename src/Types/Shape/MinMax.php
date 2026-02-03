<?php

namespace DevCoding\Reflection\Types\Shape;

/**
 * @property int $min
 * @property int $max
 * @method \ArrayIterator|int[] getIterator()
 * @method int __get($name)
 * @method int offsetGet($offset)
 */
class MinMax extends ShapeDefinition
{
  public static function pattern(string $type = 'int'): string
  {
    return '#(?<type>'.$type.')<(?<inner>[0-9]+,\s*[0-9]+)>#';
  }

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return preg_match('#(?<min>[0-9]+),\s*(?<max>[0-9]+)#', $string, $matches);
  }

  public function getMin()
  {
    return $this->getIterator()->offsetGet('min');
  }

  public function getMax()
  {
    return $this->getIterator()->offsetGet('max');
  }
}
