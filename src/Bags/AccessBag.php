<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\ReflectionAccess;
use Psr\Container\ContainerInterface;

/**
 * Container containing ReflectionAccess objects for each ReflectionProperty in a ReflectionClass
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class AccessBag implements ContainerInterface
{
  /** @var ReflectionAccess[] */
  protected $access;

  /**
   * Loops through the ReflectionProperties in the ReflectionClass and adds a ReflectionAccess object for each.
   *
   * @param \ReflectionClass $class
   */
  public function __construct(\ReflectionClass $class)
  {
    foreach($class->getProperties() as $property)
    {
      $this->access[$property->getName()] = new ReflectionAccess($property, $class);
    }
  }

  /**
   * Evaluates if the given property name corresponds to an existing property that is either public or has a standard
   * isPascalCase or getPascalCase method for access.
   *
   * @param string $property
   *
   * @return bool
   */
  public function isReadable(string $property): bool
  {
    return $this->has($property) && $this->get($property)->isReadable();
  }

  /**
   * Evaluates if the given property name corresponds to an existing property that is not read-only and is either public
   * or has a standard setPascalCase method for write access.
   *
   * @param string $property
   *
   * @return bool
   */
  public function isWritable(string $property): bool
  {
    return $this->has($property) && $this->get($property)->isWritable();
  }

  /**
   * Retrieves the current value of a property within the given object
   *
   * @param string $property
   * @param        $object
   *
   * @return null
   * @throws \ReflectionException
   */
  public function getValue(string $property, $object)
  {
    return $this->isReadable($property) ? $this->get($property)->getValue($object) : null;
  }

  /**
   * Returns an array of ReflectionAccess objects for each property that is readable in this object's ReflectionClass.
   *
   * @return ReflectionAccess[]
   */
  public function readable()
  {
    return array_filter($this->access, function($ra) { return $ra->isReadable(); });
  }

  /**
   * Returns an array of ReflectionAccess objects for each property that is writable in this object's ReflectionClass.
   *
   * @return ReflectionAccess[]
   */
  public function writable()
  {
    return array_filter($this->access, function($ra) { return $ra->isWritable(); });
  }

  /**
   * @param string $id
   *
   * @return ReflectionAccess
   */
  public function get($id)
  {
    return $this->access[$id];
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  public function has($id)
  {
    return isset($this->access[$id]);
  }
}
