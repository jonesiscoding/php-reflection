<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Base\EquatableInterface;
use DevCoding\Reflection\Types\Factory\Match;

/**
 * Interface for object classes describing a PHP type
 *
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface TypeInterface extends EquatableInterface
{
  /**
   * @return string
   */
  public function __toString();

  /**
   * MUST evaluate if the given string and Reflector can be described by this type.
   * MUST populate the given $matches argument with applicable match data
   *
   * @param string          $string
   * @param \Reflector|null $context
   * @param array{ type: string, shape: string } $matches
   *
   * @return bool
   */
  public static function match(string $string, \Reflector $context = null, array &$matches = []): bool;
}
