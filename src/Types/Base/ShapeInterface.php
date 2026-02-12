<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Types\Shape\ShapeDefinition;

interface ShapeInterface
{
  public static function addShapeDefinition(string $shapeClass);

  /**
   * @return ShapeDefinition|null
   */
  public function getShape();

  public function setShape(ShapeDefinition $shape): ShapeInterface;
}
