<?php

namespace DevCoding\Reflection\Tags;

/**
 * @author  AMJones <am@jonesiscoding.com>
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
