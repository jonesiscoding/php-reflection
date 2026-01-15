<?php

namespace DevCoding\Reflection\Tags;

/**
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionThrowsTag extends ReflectionTag
{
  public function __construct(\ReflectionClass $class, string $contents)
  {
    $property = ['tag' => 'throws'];
    if (preg_match(self::STANDARD, $contents, $tag))
    {
      $property['type']        = $tag['type'];
      $property['description'] = $tag['description'];
    }

    parent::__construct($class, array_filter($property));
  }
}
