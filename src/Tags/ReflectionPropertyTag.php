<?php

namespace DevCoding\Reflection\Tags;

/**
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionPropertyTag extends ReflectionTag implements NamedTagInterface
{
  public function __construct(\ReflectionClass $class, string $contents)
  {
    $property = ['tag' => 'property'];
    if (preg_match(self::STANDARD, $contents, $tag))
    {
      $property['name']        = $tag['name'];
      $property['type']        = $tag['type'];
      $property['description'] = $tag['description'];
    }

    parent::__construct($class, array_filter($property));
  }

  public function getName(): string
  {
    return $this->name;
  }
}
