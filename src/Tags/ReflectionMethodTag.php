<?php

namespace DevCoding\Reflection\Tags;

use DevCoding\Reflection\Bags\TagBag;

/**
 * @author  AMJones <am@jonesiscoding.com>
 */
class ReflectionMethodTag extends ReflectionTag implements NamedTagInterface
{
  const METHOD = '#(?:(?<static>static)\s+)?(?:(?<type>\S+)\s+)?(?<name>\w+)\((?<params>[^\)]+)?\)(?:\s+(?<description>.*))?#';
  const PARAM  = '#^(?:(?<type>[^$]+)\s+)?\$(?<name>\w+)(?:\s*=\s*(?<default>"[^"]+"|\[[^\]]+\]|[^,]+))?$#';

  public function __construct(\ReflectionClass $class, string $contents)
  {
    $method = ['tag' => 'method'];
    if (preg_match(self::METHOD, $contents, $tag))
    {
      $method['static']      = $tag['static'];
      $method['name']        = $tag['name'];
      $method['type']        = $tag['type'];
      $method['description'] = $tag['description'];

      if (!empty($tag['params']))
      {
        $raw = preg_split('/\s*,\s*/', $tag['params']);

        $params = [];
        foreach($raw as $p)
        {
          if (preg_match(static::PARAM, $p, $param))
          {
            $params[$param['name']] = $params;
          }
        }

        $method['params'] = $params;
      }
    }

    parent::__construct($class, array_filter($method));
  }

  /**
   * @return bool
   */
  public function isStatic(): bool
  {
    return !empty($this->static);
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return TagBag
   */
  public function getParams(): TagBag
  {
    $params = $this->params ?? [];
    if (!$params instanceof TagBag)
    {
      $params = $this->params = new TagBag($params);
    }

    return $params;
  }

  /**
   * @param string $name
   *
   * @return ReflectionTag
   */
  public function getParam(string $name): ReflectionTag
  {
    return $this->getParams()->get($name);
  }
}
