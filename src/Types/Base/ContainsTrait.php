<?php

namespace DevCoding\Reflection\Types\Base;

trait ContainsTrait
{
  public function contains(TypeInterface $find): bool
  {
    if ($this instanceof CompoundInterface)
    {
      $inner = $this->inner();
      if ($inner instanceof ContainsInterface && $inner->contains($find))
      {
        return true;
      }

      if ($inner->equals($find))
      {
        return true;
      }
    }

    if ($this instanceof ShapeInterface && $this->getShape()->contains($find))
    {
      return true;
    }

    return false;
  }

  public function replace(TypeInterface $find, TypeInterface $repl)
  {
    if ($this instanceof CompoundInterface)
    {
      $inner = $this->inner();
      if ($inner instanceof ContainsInterface)
      {
        $this->setInner($inner->replace($find, $repl));
        $inner = $this->inner();
      }

      if ($inner->equals($find))
      {
        $clone = $clone ?? clone $this;
        $clone->setInner($repl);
      }
    }

    if ($this instanceof ShapeInterface)
    {
      $shape = $this->getShape();
      if ($shape->contains($find))
      {
        $clone = $clone ?? clone $this;

        $clone->setShape($clone->getShape()->replace($find, $repl));
      }
    }

    return $clone ?? $this;
  }
}
