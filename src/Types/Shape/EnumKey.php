<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Type;

/**
 *
 */
class EnumKey extends ShapeDefinition
{
  use EnumTrait;

  /**
   * @return Type
   */
  public function getType(): Type
  {
    return $this->getIterator()->offsetGet('type');
  }

  public static function pattern(string $type = null): string
  {
    return '#key-of<(?<inner>[^>]+)>#';
  }

  /**
   * @param string          $string
   * @param \Reflector|null $context
   * @param array           $matches
   * @return bool
   * @throws \ReflectionException
   */
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    if ($enum = static::getEnum($string))
    {
      if (!$type = static::getTypeFromCases($string))
      {
        $ref   = new \ReflectionClass($enum);
        $vals  = array_values($ref->getConstants());
        $type  = gettype(array_pop($vals));
        $other = array_filter($vals, function ($v) use ($type) { return gettype($v) !== $type; });

        if (!empty($other))
        {
          throw new \ReflectionException(
            'The class '.$string.' cannot be used as an Enum; constant value types do not match.'
          );
        }
      }

      $matches = ['type' => Type::from($type, new \ReflectionClass($enum))];
    }

    if ($enum = static::getEnum($string))
    {
      $matches = ['type' => Type::from('string', new \ReflectionClass($enum))];

      return true;
    }

    return false;
  }
}
