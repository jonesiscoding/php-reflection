<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\ReflectorNotFoundException;
use DevCoding\Reflection\ReflectionString;

class NamespaceBag extends ConstructBag
{
  /** @var string[] */
  protected $directories;

  /**
   * Returns an array of directories that contain files for the given namespace name.
   *
   * @param string $id
   *
   * @return array|false;
   */
  public function directories(string $id): array
  {
    if (!isset($this->directories[$id]))
    {
      $classes = $this->get($id);
      foreach($classes as $file)
      {
        $this->directories[$id] = $file ? dirname($file) : false;
      }
    }

    return $this->directories[$id];
  }

  /**
   * Returns a ReflectionString representing the given namespace name
   *
   * @param string $id
   *
   * @return ReflectionString
   * @throws ReflectorNotFoundException
   */
  public function reflection(string $id): \Reflector
  {
    if (!$this->has($id))
    {
      throw new ReflectorNotFoundException('The namespace "' . $id . '" does not exist in this project.');
    }

    if (!isset($this->reflection[$id]))
    {
      $this->reflection[$id] = new ReflectionString($id);
    }

    return $this->reflection[$id];
  }

  /**
   * Returns an array of directories that contain files for the given namespace name
   *
   * @param string $id
   *
   * @return ClassBag
   * @throws NotFoundException
   */
  public function get(string $id)
  {
    if (!$this->has($id))
    {
      throw new ReflectorNotFoundException('The namespace "' . $id . '" does not exist in this project.');
    }

    return $this->offsetGet($id);
  }
}
