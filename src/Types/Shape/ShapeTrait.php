<?php

namespace DevCoding\Reflection\Types\Shape;

use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Factory\Factory;

trait ShapeTrait
{
  /** @var ShapeDefinition */
  protected $shape;
  /** @var array<string, class-string> */
  public static $shapes;

  public static function addShapeDefinition(string $shape)
  {
    static::$shapes[] = $shape;
  }

  public static function initializeShapeTypes()
  {
    if (!isset(static::$shapes))
    {
      static::$shapes = defined(static::class.'::SHAPES') ? constant(static::class.'::SHAPES') : [];
    }
  }

  protected static function matchShape(string $string, \Reflector $context = null, array &$matches = []): bool
  {
    static::initializeShapeTypes();
    foreach(static::$shapes as $shape)
    {
      /** @var ShapeDefinition $shape */
      if (preg_match($shape::pattern(), $string, $matches))
      {
        $matches[Factory::SHAPE] = $shape;

        return true;
      }
    }

    return false;
  }

  public function getShape()
  {
    return $this->shape;
  }

  /**
   * @param ShapeDefinition $shape
   *
   * @return ShapeInterface
   */
  public function setShape(ShapeDefinition $shape): ShapeInterface
  {
    $this->shape = $shape;

    /** @var ShapeInterface */
    return $this;
  }
}