<?php

namespace DevCoding\Reflection\Types\Base;

interface CompoundInterface extends ContainsInterface
{
  public function setInner(TypeInterface $type): CompoundInterface;

  public function inner(): TypeInterface;
}
