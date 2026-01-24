<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\TagNotFoundException;
use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Tags\TagGroup;
use Psr\Container\ContainerInterface;

/**
 * Container for ReflectionTag objects
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class TagBag extends TagGroup implements ContainerInterface
{
  /**
   * @return ReflectionTag[]
   */
  public function all()
  {
    return $this->getArrayCopy();
  }

  /**
   * @param string $id
   *
   * @return ReflectionTag
   */
  public function get($id)
  {
    if (!$this->has($id))
    {
      throw new TagNotFoundException($id);
    }

    return parent::offsetGet($id);
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  public function has($id)
  {
    return parent::offsetExists($id);
  }

  /**
   * @param ReflectionTag $tag
   *
   * @return $this
   */
  public function add(ReflectionTag $tag)
  {
    $this->append($tag);

    return $this;
  }
}
