<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Bags\ClassBag;

/**
 * Refelction style class representing a namespace
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionNamespace implements \Reflector
{
  /** @var ClassBag */
  protected $classes;
  /** @var ReflectionString */
  protected $name;
  /** @var string[] */
  protected $directories;
  /** @var bool */
  protected $resolved;

  public function __construct($namespace, ClassBag $classes = null)
  {
    $this->name     = new ReflectionString($namespace);
    $this->resolved = !empty($classes);

    if ($this->resolved)
    {
      $this->classes = $classes;
    }
  }

  /**
   * @return ClassBag
   */
  public function getClasses(): ClassBag
  {
    if (!$this->resolved)
    {
      $this->resolved = true;

      $all = (new ReflectionProject())->getNamespaces();
      if ($all->has((string) $this->name))
      {
        $this->classes = $all->get((string) $this->name);
      }
    }

    return $this->classes;
  }

  public function getName(): ReflectionString
  {
    return $this->name;
  }

  /**
   * Returns the absolute paths to the given name space in a PSR-4 directory structure
   *
   * @return array
   */
  public function getDirectories(): array
  {
    if (!isset($this->directories))
    {
      $classes = $this->getClasses();
      $dirs    = [];

      foreach($classes as $file)
      {
        if ($dir = $file ? dirname($file) : null)
        {
          $dirs[$dir] = true;
        }
      }

      $this->directories = array_keys($dirs);
    }

    return $this->directories;
  }

  public static function export()
  {
    throw new \ReflectionException('Not implemented');
  }

  public function __toString()
  {
    return (string) $this->name;
  }
}
