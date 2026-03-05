<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\Types\Base\ContainsInterface;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Factory\Match;

class Union extends Type implements \IteratorAggregate, TypeInterface, ContainsInterface
{
  /** @var \ArrayIterator */
  protected $iterator;

  /**
   * @param string          $raw
   * @param \Reflector|null $context
   *
   * @noinspection PhpMissingParentConstructorInspection
   * @throws \ReflectionException
   */
  protected function __construct(string $raw, \Reflector $context = null)
  {
    $parts = array_map(function($value) use ($context) { return Type::from($value, $context); }, explode('|', $raw));

    $this->iterator = new \ArrayIterator($parts);
    $this->raw      = $raw;
  }

  // region //////////////////////////////////////////////// Public Functions

  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    return substr_count($string, '|') > 0
           && !Nullable::match($string, $context)
           && !Prototype::match($string, $context);
  }

  /**
   * @return \ArrayIterator|Type[]
   */
  public function getIterator(): \ArrayIterator
  {
    return $this->iterator;
  }

  public function isUnion(): bool
  {
    return true;
  }

  // endregion ///////////////////////////////////////////// End Public

  // region //////////////////////////////////////////////// Equatable Interface

  /**
   * @param  Union|string $value
   * @return bool
   */
  public function equals($value): bool
  {
    $value = explode('|', (string) $value);
    $self  = explode('|', (string) $this);

    $diff1 = array_diff($value, $self);
    $diff2 = array_diff($self, $value);

    return empty($diff1) && empty($diff2);
  }

  // endregion ///////////////////////////////////////////// End EquatableInterface

  // region //////////////////////////////////////////////// ContainsInterface

  public function contains(TypeInterface $find): bool
  {
    foreach($this->getIterator() as $type)
    {
      if ($type->equals($find))
      {
        return true;
      }

      if ($type instanceof ContainsInterface && $type->contains($find))
      {
        return true;
      }
    }

    return false;
  }

  public function replace(TypeInterface $find, TypeInterface $repl)
  {
    $copy = $this->getIterator()->getArrayCopy();
    foreach($copy as $offset => $type)
    {
      if ($type->equals($find))
      {
        $clone = $clone ?? clone $this;
        $clone->getIterator()->offsetSet($offset, $repl);
      }

      if ($type instanceof ContainsInterface && $type->contains($find))
      {
        $clone = $clone ?? clone $this;
        $clone->getIterator()->offsetSet($offset, $type->replace($find, $repl));
      }
    }

    return $clone ?? $this;
  }

  // endregion ///////////////////////////////////////////// End ContainsInterface
}
