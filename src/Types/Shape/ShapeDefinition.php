<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Factory\Match;

/**
 * Base class for PHPdoc shape data and parsing
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
abstract class ShapeDefinition implements \ArrayAccess, \IteratorAggregate
{
  /** @var \ArrayIterator */
  protected $iterator;

  /**
   * MUST evaluate the given string and context to determine if they match the ShapeDefinition.
   * MUST return shape data in the $matches array if matched
   * MUST return false if not matched
   *
   * @param string          $string
   * @param \Reflector|null $context
   * @param array           $matches
   *
   * @return bool
   */
  abstract public static function match(string $string, \Reflector $context = null, array &$matches = []): bool;

  abstract public static function pattern(string $type = null): string;

  /**
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    return $this->iterator;
  }

  public function offsetExists($offset)
  {
    return $this->getIterator()->offsetExists($offset);
  }

  public function offsetGet($offset)
  {
    return $this->getIterator()->offsetGet($offset);
  }

  public function offsetSet($offset, $value)
  {
    $this->getIterator()->offsetSet($offset, $value);
  }

  public function offsetUnset($offset)
  {
    $this->getIterator()->offsetUnset($offset);
  }

  public function __get($name)
  {
    if ($this->getIterator()->offsetExists($name))
    {
      return $this->getIterator()->offsetGet($name);
    }

    return $this->{$name};
  }
}
