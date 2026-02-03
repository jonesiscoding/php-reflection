<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Types\Type;

/**
 * Base class for types that contain another type.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
abstract class Compound extends Type implements CompoundInterface
{
  /** @var Type The real Type */
  protected $inner;

  public function setInner(Type $type): CompoundInterface
  {
    $this->inner = $type;

    return $this;
  }

  /**
   * @param string                                            $string
   * @param \Reflector|null                                   $context
   * @param array{ type: string, shape: string, inner: Type } $matches
   *
   * @return bool
   */
  abstract public static function match(string $string, \Reflector $context = null, array &$matches = []): bool;

  /**
   * Returns the inner type object.
   *
   * @return Type The inner Type object
   */
  public function inner(): Type
  {
    return $this->inner;
  }
}
