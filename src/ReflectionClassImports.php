<?php

namespace DevCoding\Reflection;

/**
 * Reflection-style class which reads the imports from the given ReflectionClass to allow for type resolution from
 * short class names, as used in most PHPdocs.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionClassImports
{
  public static $parsed = [];

  /** @var \ReflectionClass */
  protected $class;

  /**
   * @param \ReflectionClass $class
   */
  public function __construct(\ReflectionClass $class)
  {
    $this->class = $class;
  }

  /**
   * Returns this object's ReflectionClass
   *
   * @return \ReflectionClass
   */
  public function getClass()
  {
    return $this->class;
  }

  /**
   * Evaluates if this object contains the fully qualified class name matching the given short name or alias.
   *
   * If a fully qualified classname is given, TRUE is automatically returned.
   *
   * @param string $offset
   *
   * @return bool
   */
  public function offsetExists($offset)
  {
    return class_exists($offset) || $this->getIterator()->offsetExists($offset);
  }

  /**
   * Returns the fully qualified class name for the given short name or alias, if that name is present in the 'use'
   * tatements of this object's ReflectionClass.
   *
   * If given a fully qualified class name of an existing class, it is returned automatically.
   *
   * @param string $offset
   *
   * @return string
   */
  public function offsetGet($offset)
  {
    return class_exists($offset) ? $offset : $this->getIterator()->offsetGet($offset);
  }

  /**
   * Adds a short name/alias => fully qualified class name pair to this iterator.
   *
   * @param string $offset
   * @param string $value
   *
   * @return void
   */
  public function offsetSet($offset, $value)
  {
    $this->getIterator()->offsetSet($offset, $value);
  }

  /**
   * Removes a fully qualified class name from this iterator, using it's alias, short name, or fqcn.
   *
   * @param string $offset
   *
   * @return void
   */
  public function offsetUnset($offset)
  {
    if (class_exists($offset))
    {
      $iterator = $this->getIterator();
      foreach($iterator as $k => $v)
      {
        if ($v === $offset)
        {
          $iterator->offsetUnset($k);

          return;
        }
      }

      return;
    }

    $this->getIterator()->offsetUnset($offset);
  }

  /**
   * @return \ArrayIterator
   */
  public function getIterator()
  {
    if (!isset($this->iterator))
    {
      $this->iterator = new \ArrayIterator($this->getImports($this->class));
    }

    return $this->iterator;
  }

  /**
   * Returns the imports for the given class. Upon first use, populates static $parsed property by parsing the given
   * ReflectionClass for 'use' imports above the 'class' declaration.
   *
   * @return string[] Short Name or Alias => Fully Qualified Class Name
   */
  private function getImports(\ReflectionClass $class): array
  {
    $name = $class->getName();
    if (!array_key_exists($name, static::$parsed))
    {
      // Add the Imports from the Class
      $imports = $this->getImportsFrom($class->getFileName());

      // Add Imports from Any Traits
      $traits = $class->getTraits();
      if (!empty($traits))
      {
        foreach($traits as $trait)
        {
          $imports = array_merge($imports, $this->getImportsFrom($trait->getFileName()));
        }
      }

      static::$parsed[$name] = $imports;
    }

    return static::$parsed[$name];
  }

  /**
   * Parses the given file for 'use' statements above the 'class' delcaration.
   *
   * @param string $file
   *
   * @return string[] Short Name or Alias => Fully Qualified Class Name
   */
  private function getImportsFrom(string $file)
  {
    $tokens = \token_get_all(file_get_contents($file));

    $useStatements = [];

    while (null !== key($tokens))
    {
      $token = \current($tokens);

      if (\is_array($token) && T_USE === $token[0])
      {
        $useStatement = [];
        while(';' != $token && '(' != $token && ')' != $token)
        {
          $useStatement[] = $token[1];
          \next($tokens);
          $token = \current($tokens);
        }

        $statement = implode('', $useStatement);
        if (!str_ends_with($statement, ';'))
        {
          $statement .= ';';
        }

        if (preg_match('/use\s+([^\s]+)(\s+as\s+(.*))?;/', $statement, $matches))
        {
          if (!$key = $matches[3] ?? null)
          {
            $key = substr(strrchr($matches[1], '\\'), 1);
          }

          $useStatements[$key] = $matches[1];
        }
      }

      \next($tokens);

      if ($token && is_array($token) && T_CLASS === $token[0])
      {
        // Stop at the class declaration.
        // No more use statements expected here
        break;
      }
    }

    return $useStatements;
  }
}
