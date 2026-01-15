<?php

namespace DevCoding\Reflection\Tags;

use DevCoding\Reflection\Helper\ArrayFirstTrait;

/**
 * Group of Tag objects. Tags that implement NamedTagInterface are added with their name as the key; other tag types
 * are added with an index key. Typically, the two are not mixed in the same TagGroup.
 *
 * @method Tag|ParamTag|MethodTag|PropertyTag offsetGet($key)
 *
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 * @author  AMJones <am@jonesiscoding.com>
 */
class TagGroup extends \ArrayIterator
{
  use ArrayFirstTrait;

  /**
   * @param ReflectionTag[]       $tags  Tags in Group
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
   * @param ReflectionTag $value
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
   * @return Tag
   */
  public function first(): Tag
  {
    return $this->array_first($this->getArrayCopy());
  }
}
