<?php

namespace DevCoding\Reflection\Comments;

use DevCoding\Reflection\Bags\TagBag;
use DevCoding\Reflection\Exceptions\TagNotFoundException;
use DevCoding\Reflection\Helper\StringStartsWithTrait;
use DevCoding\Reflection\Tags\ReflectionParamTag;
use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Tags\TagGroup;
use DevCoding\Reflection\Vars\ReflectionNamedVar;
use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection-style object representing the DocComment of a ReflectionFunctionAbstract
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionFunctionComment extends ReflectionComment
{
  use StringStartsWithTrait;

  /**
   * @param \ReflectionFunctionAbstract $function
   */
  public function __construct(\ReflectionFunctionAbstract $function)
  {
    parent::__construct($function);
  }

  /**
   * @return ReflectionTag|null
   */
  public function getReturn()
  {
    return $this->getTags()->return ?? null;
  }

  /**
   * @return ReflectionVar
   */
  public function getReturnType(): ReflectionVar
  {
    return ($r = $this->getReturn()) ? $r->type : new ReflectionNamedVar($this->reflector, 'mixed');
  }

  /**
   * @return TagBag
   */
  public function getParams(): TagBag
  {
    return $this->getTags()->param ?? new TagBag([]);
  }

  /**
   * @param string $name
   *
   * @return ReflectionParamTag
   * @throws TagNotFoundException
   */
  public function getParam(string $name): ReflectionParamTag
  {
    try
    {
      /** @var ReflectionParamTag */
      return $this->getParams()->get($name);
    }
    catch(TagNotFoundException $e)
    {
      $e->setReflector($this->reflector);

      throw $e;
    }
  }

  /**
   * @return TagGroup
   */
  public function getThrows(): TagGroup
  {
    return $this->getTags()->throws ?? new TagGroup([]);
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function hasParam(string $name): bool
  {
    return $this->getParams()->has($name);
  }

  /**
   * @param string|null $exception
   *
   * @return bool
   */
  public function isThrows($exception = null): bool
  {
    $throws = $this->getThrows();
    $count  = count($throws);
    if (0 === $count)
    {
      return false;
    }
    elseif (!isset($exception))
    {
      return $count > 0;
    }

    if ($this->str_starts_with($exception, '\\'))
    {
      $exception = substr($exception, 1);
    }

    foreach($throws as $throw)
    {
      if ($exception === $throw->getType()->getName())
      {
        return true;
      }
    }

    return false;
  }
}
