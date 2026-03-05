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
  use ContainsTrait;

  /** @var Type The real Type */
  protected $inner;

  // region //////////////////////////////////////////////// Abstract Functions

  /**
   * @param string                                            $string
   * @param \Reflector|null                                   $context
   * @param array{ type: string, shape: string, inner: Type } $matches
   *
   * @return bool
   */
  abstract public static function match(string $string, \Reflector $context = null, array &$matches = []): bool;

  // endregion ///////////////////////////////////////////// End Abstract Functions

  // region //////////////////////////////////////////////// Equatable Interface

  /**
   * @param  CompoundInterface|string $value
   * @return bool
   */
  public function equals($value): bool
  {
    return $this->inner()->equals($value);
  }

  // endregion ///////////////////////////////////////////// End Equatable Interface

  // region //////////////////////////////////////////////// CompoundInterface

  /**
   * Returns the inner type object.
   *
   * @return Type The inner Type object
   */
  public function inner(): TypeInterface
  {
    return $this->inner;
  }

  public function setInner(TypeInterface $type): CompoundInterface
  {
    $this->inner = $type;

    return $this;
  }

  // endregion ///////////////////////////////////////////// End CompoundInterface
}
