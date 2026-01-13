<?php

namespace DevCoding\Reflection\Comments;

use DevCoding\Reflection\Bags\TagBag;
use DevCoding\Reflection\Exceptions\TagNotFoundException;
use DevCoding\Reflection\Tags\ReflectionMethodTag;
use DevCoding\Reflection\Tags\ReflectionPropertyTag;

/**
 * Reflection-style object representing the DocComment of a ReflectionClass
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionClassComment extends ReflectionComment
{
  public function __construct(\ReflectionClass $ReflectionClass)
  {
    parent::__construct($ReflectionClass);
  }

  /**
   * @param string $name
   *
   * @return ReflectionMethodTag
   * @throws TagNotFoundException
   */
  public function getMethod(string $name): ReflectionMethodTag
  {
    try
    {
      /** @var ReflectionMethodTag */
      return $this->getMethods()->get($name);
    }
    catch(TagNotFoundException $e)
    {
      $e->setReflector($this->reflector);

      throw $e;
    }
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function hasMethod(string $name): bool
  {
    return $this->getMethods()->has($name);
  }

  /**
   * @param string $name
   *
   * @return ReflectionPropertyTag
   * @throws TagNotFoundException
   */
  public function getProperty(string $name): ReflectionPropertyTag
  {
    try
    {
      /** @var ReflectionPropertyTag */
      return $this->getProperties()->get($name);
    }
    catch(TagNotFoundException $e)
    {
      $e->setReflector($this->reflector);

      throw $e;
    }
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function hasProperty(string $name): bool
  {
    return $this->getProperties()->has($name);
  }

  /**
   * @return TagBag
   */
  protected function getProperties(): TagBag
  {
    return $this->getTags()->property ?? new TagBag([]);
  }

  /**
   * @return TagBag
   */
  protected function getMethods(): TagBag
  {
    return $this->getTags()->method ?? new TagBag([]);
  }
}
