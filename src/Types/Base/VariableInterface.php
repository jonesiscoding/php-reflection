<?php

namespace DevCoding\Reflection\Types\Base;

interface VariableInterface
{
  public function setType(string $type): VariableInterface;
}