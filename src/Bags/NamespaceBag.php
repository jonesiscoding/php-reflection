<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\ReflectorNotFoundException;
use DevCoding\Reflection\ReflectionString;

class NamespaceBag extends ConstructBag
{
  /**
   * Returns an array of directories that contain files for the given namespace name.
   *
   * @param string $id
   *
   * @return array|false;
   */
  public function directories(string $id): array
  {
    return $this->get($id);
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
   * @return array
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
