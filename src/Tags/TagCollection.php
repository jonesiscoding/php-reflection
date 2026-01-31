<?php

namespace DevCoding\Reflection\Tags;

use DevCoding\Reflection\Bags\TagBag;
use DevCoding\Reflection\ReflectionString;

/**
 * Collection of ReflectionTag objects, grouped by TagGroup or TagBag.
 *
 * @property TagBag|ReflectionMethodTag[]|null   $method
 * @property TagBag|ReflectionPropertyTag[]|null $property
 * @property TagBag|ReflectionParamTag[]|null    $param
 * @property TagGroup|ReflectionTag[]|null       $throws
 * @property TagGroup|ReflectionTag[]|null       $uses
 * @property TagGroup|ReflectionTag[]|null       $usedBy
 * @property ReflectionTag|null                  $api
 * @property ReflectionTag|null                  $ignore
 * @property ReflectionTag|null                  $internal
 * @property ReflectionTag|null                  $required
 * @property ReflectionTag|null                  $return
 * @property ReflectionTag|null                  $default
 * @property ReflectionTag|null                  $summary
 * @property ReflectionTag|null                  $description
 * @property ReflectionTag|null                  $var
 *
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 * @author  AMJones <am@jonesiscoding.com>
 */
class TagCollection extends \ArrayIterator
{
  const SINGLE = ['api','default','description','ignore','internal','required','return','summary','var'];

  /**
   * @param ReflectionTag[] $tags
   */
  public function __construct($tags)
  {
    parent::__construct([], \ArrayIterator::ARRAY_AS_PROPS);

    foreach($tags as $tag)
    {
      $this->append($tag);
    }
  }

  /**
   * Evaluates if the given tag name exists in this collection.
   *
   * @param string $key Tag Name
   *
   * @return bool
   */
  public function offsetExists($key)
  {
    return parent::offsetExists($this->offsetNormalize($key));
  }

  /**
   * Returns a TagGroup or Tag based on the given tag name.  Tags that implement RecurrentTagInterface will be returned
   * as a TagGroup.
   *
   * @param string $key Tag Name
   *
   * @return ReflectionTag|TagGroup
   */
  public function offsetGet($key)
  {
    /** @var TagGroup $group */
    $group = parent::offsetGet($this->offsetNormalize($key));
    $first = 1 === count($group) ? $group->first() : null;

    return $first && in_array($first->tag, self::SINGLE) ? $first : $group;
  }

  /**
   * Normalizes the given tag name by camelizing it.
   *
   * @param string $key   The raw tag name (IE - property-write)
   *
   * @return string       The camelized tag name (IE - propertyWrite)
   */
  public function offsetNormalize($key): string
  {
    return (new ReflectionString($key))->camel();
  }

  /**
   * Throws a BadMethodCallException, as you cannot set a value in a TagCollection after instantiation.
   * The append method can be used to properly add tags.
   *
   * @param string $key
   * @param Tag    $value
   *
   * @throws \BadMethodCallException
   */
  public function offsetSet($key, $value)
  {
    throw new \BadMethodCallException(sprintf('You cannot set the %s key of %s', $key, get_class($this)));
  }

  /**
   * Throws a BadMethodCallException, as you cannot unset a value in a TagCollection after instantiation.
   *
   * @param string $key
   *
   * @return mixed
   */
  public function offsetUnset($key)
  {
    throw new \BadMethodCallException(sprintf('You cannot unset the %s key of %s', $key, get_class($this)));
  }

  /**
   * Appends a value to this TagCollection by normalizing the tag name and grouping any tags using RecurrentTagInterface
   * or inheriting AnnotationObjectTag.
   *
   * @param ReflectionTag $value
   *
   * @return void
   */
  public function append($value)
  {
    if ($value instanceof ReflectionTag)
    {
      if ($value instanceof AnnotationObjectTag)
      {
        $tag = 'annotations';
      }
      else
      {
        $tag = $value->tag;
      }

      if (!$this->offsetExists($tag))
      {
        if ($value instanceof NamedTagInterface)
        {
          parent::offsetSet($tag, new TagBag([$value]));
        }
        else
        {
          parent::offsetSet($tag, new TagGroup([$value]));
        }
      }
      else
      {
        parent::offsetGet($tag)->append($value);
      }
    }
  }
}
