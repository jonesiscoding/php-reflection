<?php

namespace DevCoding\Reflection\Comments;

use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection-style object representing the DocComment of a ReflectionProperty
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionPropertyComment extends ReflectionComment
{
  /**
   * @param \ReflectionProperty $ReflectionProperty
   */
  public function __construct(\ReflectionProperty $ReflectionProperty)
  {
    parent::__construct($ReflectionProperty);
  }

  public function getType(): ReflectionVar
  {
    return $this->getVar()->type;
  }

  /**
   * @return ReflectionTag
   */
  public function getVar(): ReflectionTag
  {
    $var = $this->tags['var'] ?? 'mixed';
    if (!$var instanceof ReflectionTag)
    {
      $ret = ['tag' => 'var'];
      if (preg_match(ReflectionTag::STANDARD, $var, $tag))
      {
        $ret['type']        = $tag['type'];
        $ret['description'] = $tag['description'];
      }

      $var = $this->tags['return'] = new ReflectionTag($this->reflector, $ret);
    }

    return $var;
  }
}
