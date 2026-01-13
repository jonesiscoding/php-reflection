<?php

namespace DevCoding\Reflection\Tags;

class ReflectionParamTag extends ReflectionTag implements NamedTagInterface
{
  public function __construct(\ReflectionClass $class, string $contents)
  {
    $property = ['tag' => 'param'];
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
