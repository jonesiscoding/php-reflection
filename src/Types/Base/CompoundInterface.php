<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Types\Type;

interface CompoundInterface extends TypeInterface
{
  public function setInner(Type $type): CompoundInterface;

  public function inner(): Type;
}
