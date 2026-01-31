<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\ReflectorNotFoundException;
use DevCoding\Reflection\ReflectionNamespace;

/**
 * Container for constructs such as classes, namespaces, and interfaces
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
abstract class ConstructBag extends \ArrayIterator
{
  /** @var \SplFileInfo[] */
  protected $files;
  /** @var \ReflectionClass[]|ReflectionNamespace[] */
  protected $reflection;

  /**
   * MUST return a ReflectionConstruct matching the given construct name.
   * MUST throw an exception if the construct is not in this container.
   *
   * @param string $id
   *
   * @return mixed
   * @throws ReflectorNotFoundException
   */
  abstract public function get(string $id);

  /**
   * MUST return a Refelector representing the given construct string.
   * MUST throw an exception if the construct is not in this container.
   *
   * @param string $id
   *
   * @return \Reflector
   * @throws ReflectorNotFoundException
   */
  abstract public function reflection(string $id): \Reflector;

  /**
   * Evaluates if this container has a construct matching the given name
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
   * Merges the given array of construct names into this container.
   *
   * @param array $array
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
}
