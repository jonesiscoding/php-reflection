<?php

namespace DevCoding\Reflection\Comments;

use DevCoding\Reflection\Bags\TagBag;
use DevCoding\Reflection\Exceptions\TagNotFoundException;
use DevCoding\Reflection\Tags\ReflectionParamTag;
use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Tags\TagGroup;
use DevCoding\Reflection\Types\Base\CompoundInterface;
use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Type;
use DevCoding\Reflection\Types\Union;

/**
 * Reflection-style object representing the DocComment of a ReflectionFunctionAbstract
 *
 * @property \ReflectionFunctionAbstract $reflector
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionFunctionComment extends ReflectionComment
{
  /**
   * @param \ReflectionFunctionAbstract $function
   */
  public function __construct(\ReflectionFunctionAbstract $function)
  {
    parent::__construct($function);
  }

  /**
   * Returns an array of imports required by the parameters and return type of this ReflectionFunctionComment
   *
   * @return string[]
   */
  public function getImports(): array
  {
    $imports = [];

    $this->addImports($this->getReturnType(), $imports);
    foreach($this->getParams() as $param)
    {
      if (isset($param->type))
      {
        $this->addImports($param->type, $imports);
      }
    }

    return array_keys($imports);
  }

  /**
   * @return ReflectionTag|null
   */
  public function getReturn()
  {
    return $this->getTags()->return ?? null;
  }

  /**
   * @return Type
   */
  public function getReturnType(): Type
  {
    return ($r = $this->getReturn()) ? $r->type : Type::from('mixed', $this->reflector);
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

    if (str_starts_with($exception, '\\'))
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

  /**
   * @param Type $type
   * @param array $imports
   *
   * @return $this
   */
  protected function addImports($type, &$imports)
  {
    if (isset($type))
    {
      if ($type instanceof Union)
      {
        foreach($type as $t)
        {
          $this->addImports($t, $imports);
        }
      }
      elseif ($type instanceof CompoundInterface)
      {
        $this->addImports($type->inner(),$imports);
      }
      elseif(!$type->isBuiltin())
      {
        $imports[(string) $type] = true;
      }

      if ($type instanceof ShapeInterface)
      {
        if ($definition = $type->getShape())
        {
          foreach($definition as $item)
          {
            if ($item instanceof Type)
            {
              $this->addImports($item, $imports);
            }
          }
        }
      }
    }

    return $this;
  }
}
