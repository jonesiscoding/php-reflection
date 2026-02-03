<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Types\Factory\Match;

interface TypeInterface
{
  /**
   * @param string          $string
   * @param \Reflector|null $context
   * @param array{ type: string, shape: string } $matches
   *
   * @return bool
   */
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool;
}
