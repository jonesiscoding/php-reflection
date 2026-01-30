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

  /**
   * @param $string
   *
   * @return string[]
   */
  public static function explodeClass($string): array
  {
    $short = substr(strrchr($string, '\\'), 1);

    return [substr($string, 0, -(strlen($short) + 1)), $short];
  }

}
