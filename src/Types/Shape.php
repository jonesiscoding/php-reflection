<?php

namespace DevCoding\Reflection\Types;

class Shape
{
  /** @var string */
  public $key;
  /** @var bool  */
  public $optional;
  /** @var Type */
  public $type;

  protected $pattern = '#?<key>\w+)(?<optional>\?)?\s*:\s*(?<type>[^,]+#';

  /**
   * @param string      $string
   * @param \Reflector  $context
   * @param string|null $pattern
   */
  protected function __construct(string $string, \Reflector $context, string $pattern = null)
  {
    $this->pattern = $pattern ?? $this->pattern;

    if(preg_match($pattern, $string, $m))
    {
      $this->key      = $m['key'];
      $this->optional = !empty($m['optional']);
      $this->type     = Type::from($m['type'], $context);
    }
    else
    {
      throw new \InvalidArgumentException('The given string could not be parsed into a PHP Shape.');
    }
  }

  /**
   * @param string      $string
   * @param \Reflector  $context
   * @param string|null $pattern
   *
   * @return Shape
   * @throws \InvalidArgumentException
   */
  public static function from(string $string, \Reflector $context, string $pattern = null)
  {
    return new self($string, $context, $pattern);
  }

  /**
   * @param string      $string
   * @param \Reflector  $context
   * @param string|null $pattern
   *
   * @return Shape|null
   */
  public static function tryFrom(string $string, \Reflector $context, string $pattern = null)
  {
    try
    {
      return static::from($string, $context, $pattern);
    }
    catch(\Throwable $t)
    {
      return null;
    }
  }
}
