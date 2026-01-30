<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\NotFoundException;
use DevCoding\Reflection\Exceptions\ReflectorNotFoundException;
use DevCoding\Reflection\ReflectionClassName;

/**
 * Container for fully qualified classes found within a project
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ClassBag extends ConstructBag
{
  /** @var \ReflectionClass[] */
  protected $reflection;
  /** @var ReflectionClassName */
  protected $names;
  /** @var \SplFileInfo[] */
  protected $files;

  /**
   * Returns an array of all classes as strings.
   *
   * @return string[]
   */
  public function classes()
  {
    return array_keys($this->getArrayCopy());
  }

  /**
   * Evaluates if this container has the class given by fully qualified class name.
   *
   * @param string $id
   *
   * @return bool
   */
  public function has($id): bool
  {
    return parent::offsetExists($id);
  }

  /**
   * Merges the given array of fully qualified class names into this container.
   *
   * @param string[] $array
   *
   * @return $this
   */
  public function merge($array)
  {
    foreach($array as $key => $value)
    {
      $this->offsetSet($key, $value);
    }

    return $this;
  }

  /**
   * Returns a file object for the given fully qualified class name
   *
   * @param string $id
   *
   * @return \SplFileInfo|false
   */
  public function file(string $id)
  {
    if (!isset($this->files[$id]))
    {
      $file = $this->get($id);

      $this->files[$id] = $file ? new \SplFileInfo($file) : false;
    }

    return $this->files[$id];
  }

  /**
   * Returns a RelfectionClass for the given fully qualified class name.
   *
   * @param string $id
   *
   * @return \ReflectionClass
   * @throws ReflectorNotFoundException
   */
  public function reflection(string $id): \Reflector
  {
    if (!isset($this->reflection[$id]))
    {
      if ($this->has($id))
      {
        $this->reflection[$id] = new \ReflectionClass($id);
      }
      else
      {
        throw new ReflectorNotFoundException($id);
      }
    }

    return $this->reflection[$id];
  }

  /**
   * Returns a ReflectionClassName object for the given fully qualified class name.
   *
   * @param string $id
   *
   * @return ReflectionClassName
   */
  public function name(string $id): ReflectionClassName
  {
    if (!isset($this->names[$id]))
    {
      $this->names[$id] = new ReflectionClassName($this->reflection($id));
    }

    return $this->names[$id];
  }

  /**
   *
   * @param string $id
   *
   * @return string
   */
  public function get(string $id)
  {
    if (!$this->has($id))
    {
      throw new NotFoundException('The class '.$id.' does not exist in this project.');
    }

    return $this->offsetGet($id);
  }
}