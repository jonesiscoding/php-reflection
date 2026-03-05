<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Base\ContainsInterface;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Factory\Match;

/**
 * Base class for PHPdoc shape data and parsing
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
abstract class ShapeDefinition implements ContainsInterface, \ArrayAccess, \IteratorAggregate
{
  /** @var \ArrayIterator */
  protected $iterator;

  // region //////////////////////////////////////////////// Abstract Functions

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

  // endregion ///////////////////////////////////////////// End Abstract Functions

  // region //////////////////////////////////////////////// Iterator & Array

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

  // endregion ///////////////////////////////////////////// End Iterator & Array

  // region //////////////////////////////////////////////// ContainsInterface

  /**
   * @param TypeInterface $find
   * @return bool
   */
  public function contains(TypeInterface $find): bool
  {
    foreach($this as $value)
    {
      if ($value instanceof TypeInterface)
      {
        if ($value instanceof ContainsInterface && $value->contains($find))
        {
          return true;
        }

        if ($value->equals($find))
        {
          return true;
        }
      }
    }

    return false;
  }

  public function replace(TypeInterface $find, TypeInterface $repl)
  {
    if ($this->contains($find))
    {
      $clone = clone $this;
      foreach($clone as $key => $value)
      {
        if ($value instanceof TypeInterface)
        {
          if ($value instanceof ContainsInterface)
          {
            $clone->offsetSet($key, $value->replace($find, $repl));
          }
          elseif ($value->equals($find))
          {
            $clone->offsetSet($key, $repl);
          }
        }
      }

      return $clone;
    }

    return $this;
  }

  // endregion ///////////////////////////////////////////// End ContainsInterface
}
