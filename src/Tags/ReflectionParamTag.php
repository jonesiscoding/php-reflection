<?php

namespace DevCoding\Reflection\Tags;

use DevCoding\Reflection\Types\Type;

/**
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionParamTag extends ReflectionTag implements NamedTagInterface
{
  public function __construct(\ReflectionMethod $method, string $contents)
  {
    $property = ['tag' => 'param'];
    if (preg_match(self::STANDARD, '@param '.$contents, $tag))
    {
      $property['name']        = $tag['name'];
      $property['description'] = $tag['description'] ?? null;

      if (!empty($tag['type']))
      {
        $property['type'] = Type::from($tag['type'], $method);
      }
    }

    parent::__construct($method, array_filter($property));
  }

  public function getName(): string
  {
    return $this->name;
  }
}
