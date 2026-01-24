<?php

namespace DevCoding\Reflection;

class ReflectionString implements \Reflector
{
  /** @var string */
  protected $string;

  public function __construct(string $string)
  {
    $this->string = $string;
  }

  public static function export()
  {
  }

  public function __toString()
  {
    return $this->string;
  }
}
