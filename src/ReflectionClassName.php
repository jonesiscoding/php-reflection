<?php

namespace DevCoding\Reflection;

/**
 * Reflection style class representing the name of a class
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionClassName extends ReflectionString
{
  /** @var string */
  protected $namespace;
  /** @var string */
  protected $short;

  /**
   * @return bool
   */
  public function exists()
  {
    return class_exists($this->string);
  }

  /**
   * Returns the fully qualified class name
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->string;
  }

  /**
   * Returns the short class name without the namespace
   *
   * @return string
   */
  public function getShortName()
  {
    if (!isset($this->short))
    {
      try
      {
        if ($this->reflector instanceof \ReflectionClass)
        {
          $this->short = $this->reflector->getShortName();
        }
        else
        {
          $this->short = (ReflectionContext::from($this->reflector))->getShortName();
        }
      }
      catch (\Exception $e)
      {
        $this->short = substr(strrchr($this->string, '\\'), 1);
      }
    }

    return $this->short;
  }

  /**
   * Returns the namespace without the short class name.
   *
   * @return string
   */
  public function getNamespace()
  {
    if (!isset($this->namespace))
    {
      try
      {
        if ($this->reflector instanceof \ReflectionClass)
        {
          $this->namespace = $this->reflector->getNamespaceName();
        }
        else
        {
          $this->namespace = (ReflectionContext::from($this->reflector))->getNamespaceName();
        }
      }
      catch (\Exception $e)
      {
        $this->namespace = str_replace($this->getName().'\\', '', $this->string);
      }
    }

    return $this->namespace;
  }
}
