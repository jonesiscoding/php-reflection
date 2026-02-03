<?php

namespace DevCoding\Reflection\Types\Base;

use DevCoding\Reflection\Types\Shape\ShapeDefinition;

interface ShapeInterface
{
  public static function addShapeDefinition(string $shapeClass);

  public function getShape(): ShapeDefinition;

  public function setShape(ShapeDefinition $shape): ShapeInterface;
}
