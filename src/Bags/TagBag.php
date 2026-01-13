<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Exceptions\TagNotFoundException;
use DevCoding\Reflection\Tags\ReflectionTag;
use Psr\Container\ContainerInterface;

/**
 * Container for ReflectionTag objects
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class TagBag implements ContainerInterface
{
  /** @var ReflectionTag */
  protected $tags;

  /**
   * @param ReflectionTag[] $tags
   */
  public function __construct(array $tags)
  {
    $this->tags = $tags;
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

    return $this->tags[$id];
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  public function has($id)
  {
    return isset($this->tags[$id]);
  }

  /**
   * @param ReflectionTag $tag
   *
   * @return $this
   */
  public function add(ReflectionTag $tag)
  {
    $this->tags[$tag->name] = $tag;

    return $this;
  }
}
