<?php

namespace DevCoding\Reflection\Comments;

use DevCoding\Reflection\Tags\ReflectionMethodTag;
use DevCoding\Reflection\Tags\ReflectionParamTag;
use DevCoding\Reflection\Tags\ReflectionPropertyTag;
use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Tags\TagCollection;

/**
 * Reflection-style object representing the DocComment of a Reflector
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionComment
{
  const NAMED = ['method','param','property','property-write','property-read'];

  protected $reflector;
  /** @var TagCollection */
  protected $tags;

  /**
   * @param $reflector
   */
  public function __construct($reflector)
  {
    $this->reflector = $reflector;

    $comment   = $this->reflector->getDocComment();
    $comment   = preg_replace('#(^\s*/?\*{0,2}|^\s*\*\s*|\s+\*/)#m', '$2', $comment);
    $notations = $this->extractNotations($comment);
    $notations = $this->joinMultilineNotations($notations);

    foreach($notations as $notation)
    {
      $this->tags[$notation['tag']][] = $notation['value'];
    }

    if (!empty($summary = $this->extractSummary($comment)))
    {
      $this->tags['summary'] = new ReflectionTag($this->reflector, $summary);
    }
  }

  /**
   * @return ReflectionTag|null
   */
  public function getSummary()
  {
    $tags = $this->getTags();

    return $tags->offsetExists('summary') ? $tags->offsetGet('summary') : null;
  }

  public function getTags(): TagCollection
  {
    $all = $this->tags ?? [];
    if (!$all instanceof TagCollection)
    {
      $this->tags = new TagCollection([]);
      foreach($all as $name => $tags)
      {
        foreach($tags as $tag)
        {
          if ('method' === $name)
          {
            $this->tags->append(new ReflectionMethodTag($this->reflector, $tag));
          }
          elseif ('property' === $name)
          {
            $this->tags->append(new ReflectionPropertyTag($this->reflector, $tag));
          }
          elseif ('param' === $name)
          {
            $this->tags->append(new ReflectionParamTag($this->reflector, $tag));
          }
          else
          {
            $match = ['tag' => $name];
            if (preg_match(ReflectionTag::STANDARD, $tag, $match))
            {
              $this->tags->append(new ReflectionTag($this->reflector, $match));
            }
          }
        }
      }
    }

    return $this->tags;
  }

  /**
   * Extract notation from doc comment
   *
   * @param string $doc
   * @return array
   */
  protected function extractNotations(string $doc): array
  {
    $matches = null;

    $tag         = '\s*@(?<tag>\S+)(?:\h+(?<value>\S.*?)|\h*)';
    $tagContinue = '(?:\040){2}(?<multiline_value>\S.*?)';
    $regex       = '/^\s*(?:(?:\/\*)?\*)?(?:' . $tag . '|' . $tagContinue . ')(?:\s*\*\*\/)?\r?$/m';

    return preg_match_all($regex, $doc, $matches, PREG_SET_ORDER) ? $matches : [];
  }

  /**
   * @param string $doc
   *
   * @return array
   */
  protected function extractSummary(string $doc)
  {
    $comment = preg_replace('/(^\/[\*]{1,2}\n*|\s?\*\/\s*$)/m', '', $doc);
    $retval  = ['tag' => 'summary'];
    if(preg_match_all('/^\s*(?:(?:\/\*)?\*\s*)?([^@\s\/*].*?|$)\r?$/m', $comment, $matches))
    {
      if (!empty($matches[1]))
      {
        $matches = $matches[1];

        if (count($matches) > 2)
        {
          if (!empty($matches[1]) && empty($matches[2]))
          {
            $retval['summary'] = array_shift($matches);
          }

          $retval['description'] = trim(implode(' ', $matches));
        }
      }
    }

    return $retval;
  }

  /**
   * Join multiline notations
   *
   * @param array $rawNotations
   * @return array
   */
  protected function joinMultilineNotations(array $rawNotations): array
  {
    $result        = [];
    $tagsNotations = $this->filterTagsNotations($rawNotations);

    foreach ($tagsNotations as $item)
    {
      if ('' !== $item['tag'])
      {
        $result[] = $item;
      }
      else
      {
        $lastIdx = count($result) - 1;

        if (!isset($result[$lastIdx]['value']))
        {
          $result[$lastIdx]['value'] = '';
        }

        $result[$lastIdx]['value'] = trim($result[$lastIdx]['value']) . ' ' . trim($item['multiline_value']);
      }
    }

    return $result;
  }

  /**
   * Remove everything that goes before tags
   *
   * @param array $rawNotations
   * @return array
   */
  protected function filterTagsNotations(array $rawNotations): array
  {
    for ($i = 0; $i < count($rawNotations); $i++)
    {
      if ('' !== $rawNotations[$i]['tag'])
      {
        return array_slice($rawNotations, $i);
      }
    }

    return [];
  }
}
