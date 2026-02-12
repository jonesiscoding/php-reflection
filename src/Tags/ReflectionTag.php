<?php

namespace DevCoding\Reflection\Tags;

use DevCoding\Reflection\Bags\TagBag;
use DevCoding\Reflection\Types\Type;

/**
 * Reflection style object containing data from a parsed PHPdoc tag.
 *
 * @property string|null        $tag          Name of the tag
 * @property Type|null          $type         Type of property or parameter, return type of method
 * @property TagBag|null        $params       Array of parameters from a method
 *
 * @property string|null        $description  Freeform description text
 * @property string|null        $name         Name of parameter or variable, if present
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionTag extends \ArrayIterator
{
  const STANDARD     = '#@(?<tag>var|param|property)\s+(?<type>[^\s\$]+)?\s*(?:\$(?<name>[^\s]+))?(?:\s+(?<description>.+))?#';
  const METHOD       = '#@(?<tag>method)\s+(?:(?<static>static)\s+)?(?:(?<type>\S+)\s+)?(?<name>\w+)\((?<params>[^\)]+)?\)(?:\s+(?<description>.*))?#';
  const METHOD_PARAM = '#^(?:(?<type>[^$]+)\s+)?\$(?<name>\w+)(?:\s*=\s*(?<default>"[^"]+"|\[[^\]]+\]|[^,]+))?$#';

  /**
   * @param \ReflectionProperty|\ReflectionFunctionAbstract|\ReflectionClass        $reflector
   * @param array{ type: string, description: string, params: array, name: string } $tag
   */
  public function __construct(\Reflector $reflector, array $tag = [])
  {
    if ($t = $tag['type'] ?? null)
    {
      $tag['type'] = Type::tryFrom($t);
    }

    if ($params = $tag['params'] ?? null)
    {
      foreach($params as $name => $param)
      {
        $params[$name] = new ReflectionTag($reflector, $param);
      }

      $tag['params'] = new TagBag($params);
    }

    parent::__construct($tag, \ArrayIterator::ARRAY_AS_PROPS);
  }

  /**
   * @param \Closure   $Closure     Closure to extract the tag attributes, with a single parameter of the Reflector
   * @param \Reflector $Reflector   Reflector from which to extract the tag attributes
   *
   * @return ReflectionTag|null     If the closure returns attributes, the ReflectionTag object, otherwise null
   */
  public static function fromClosure(\Closure $Closure, \Reflector $Reflector)
  {
    if ($attributes = $Closure($Reflector))
    {
      return new ReflectionTag($Reflector, $attributes);
    }

    return null;
  }

  /**
   * @param string      $d        DocComment text
   * @param string|null $variable Optional variable name to match
   * @param string      $pattern  Optional pattern; defaults to ReflectionTag::STANDARD
   *
   * @return array{type: string, variable: string|null, description: string|null, params: array|null}
   */
  public static function extract(string $d, $variable = null, string $pattern = self::STANDARD)
  {
    if (isset($variable))
    {
      if (preg_match_all($pattern, $d, $matches, PREG_SET_ORDER))
      {
        foreach($matches as $m)
        {
          if ($variable === $m['name'] ?? null)
          {
            if (self::METHOD === $pattern)
            {
              $raw = preg_split('/\s*,\s*/', $m['params']);

              $m['params'] = [];
              foreach($raw as $p)
              {
                if ($param = static::extract($p, null, self::METHOD_PARAM))
                {
                  $m['params'][$param['name']] = $param;
                }
              }
            }

            return $m;
          }
        }
      }
    }
    elseif (preg_match($pattern, $d, $m))
    {
      return $m;
    }

    return null;
  }
}
