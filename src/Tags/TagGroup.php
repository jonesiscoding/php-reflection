<?php

namespace DevCoding\Reflection\Tags;

/**
 * Group of Tag objects. Tags that implement NamedTagInterface are added with their name as the key; other tag types
 * are added with an index key. Typically, the two are not mixed in the same TagGroup.
 *
 * @method ReflectionTag|ReflectionMethodTag|ReflectionParamTag|ReflectionPropertyTag offsetGet($key)
 *
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 * @author  AMJones <am@jonesiscoding.com>
 */
class TagGroup extends \ArrayIterator
{
  /**
   * @param ReflectionTag[]|ReflectionMethodTag[]|ReflectionParamTag[]|ReflectionPropertyTag[] $tags  Tags in Group
   */
  public function __construct(array $tags)
  {
    $rsv = [];
    foreach($tags as $tag)
    {
      $this->append($tag);
    }

    parent::__construct($rsv);
  }

  /**
   * @param ReflectionTag|ReflectionMethodTag|ReflectionParamTag|ReflectionPropertyTag $value
   *
   * @return void
   */
  public function append($value)
  {
    if ($value instanceof ReflectionTag)
    {
      if (isset($value->name))
      {
        parent::offsetSet($value->name, $value);
      }
      else
      {
        parent::append($value);
      }
    }
  }

  /**
   * @return ReflectionTag|ReflectionMethodTag|ReflectionParamTag|ReflectionPropertyTag
   */
  public function first(): ReflectionTag
  {
    return array_first($this->getArrayCopy());
  }
}
