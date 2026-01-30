<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Vars\ReflectionNamedVar;
use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection-style class for manipulation of a construct string representing class, property, parameter, or function.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionConstruct implements \Reflector
{
  const GET            = 'get';
  const SET            = 'set';
  const IS             = 'is';
  const HAS            = 'has';
  const SCOPE          = '::';
  const OBJECT         = '->';
  const DOLLAR         = '$';
  const PATTERN_PASCAL = ' _-';
  const PATTERN_SNAKE  = '~(?<=\\w)([A-Z])~';

  /** @var \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass|\ReflectionParameter|ReflectionString */
  protected $reflector;
  /** @var string Original raw string */
  protected $string;
  /** @var string Scope or Object separator */
  protected $separator;
  /** @var string Class Name */
  protected $class;
  /** @var string Method, Property, or Constant Name */
  protected $member;
  /** @var string Member Prefix*/
  protected $prefix;
  /** @var string Member Suffix */
  protected $suffix;
  /** @var int Cache for Expensive Method */
  private $is_namespace = 0;
  /** @var int Cache for Expensive Method */
  private $is_trait = 0;

  /**
   * @param string $string
   */
  public function __construct(string $string)
  {
    $this->string = $string;

    if (preg_match(
      '^([a-zA-Z_\x21-\x7E][a-zA-Z0-9_\x21-\x7E]*)((?:->|::))(\$?[a-zA-Z_\x21-\x7E][a-zA-Z0-9_\x21-\x7E]*)\(?.*\)?)?$',
      $this->string,
      $m
    ))
    {
      $this->class     = $m[1];
      $this->separator = $m[2];
      $this->member    = $m[3];
      $this->suffix    = $m[4] ?? null;

      if (str_starts_with($this->member, '$'))
      {
        $this->prefix = '$';
        $this->member = substr($this->member, 1);
      }
    }
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->string;
  }

  /**
   * @return \ReflectionMethod|\ReflectionClass|\ReflectionProperty|ReflectionNamespace|\Reflector
   * @throws \ReflectionException
   */
  public function getReflector(): \Reflector
  {
    if (!isset($this->reflector))
    {
      if ($this->isClass() || $this->isInterface() || $this->isEnum())
      {
        return new \ReflectionClass($this->string);
      }
      elseif ($this->isMethod() || $this->isMethodLike())
      {
        return new \ReflectionMethod($this->class, $this->member);
      }
      elseif ($this->isProperty() || $this->isPropertyLike())
      {
        return new \ReflectionProperty($this->class, $this->member);
      }
      elseif ($this->isConstant() || $this->isConstantLike())
      {
        if (class_exists('\ReflectionClassConstant'))
        {
          return new \ReflectionClassConstant($this->class, $this->member);
        }

        return $this;
      }
      elseif ($this->isNamespace())
      {
        return new ReflectionNamespace($this->class);
      }
    }

    return $this;
  }

  public static function export()
  {
    throw new \ReflectionException(\Reflector::class . '::export is deprecated and was not implemented in '.__CLASS__);
  }

  public function __toString()
  {
    return $this->string;
  }

  // region //////////////////////////////////////////////// String Methods

  /**
   * Converts this object into a camelCase string  (IE - Converts 'class_name' to 'className')
   *
   * @return string
   */
  public function camel(): string
  {
    return lcfirst($this->pascal());
  }

  /**
   * Returns the expected setter method name, if this object's reflector is a ReflectionProperty
   *
   * @param string $prefix
   * @param bool   $normalize
   *
   * @return string
   */
  public function method($prefix = self::GET, $normalize = true): string
  {
    if ($this->reflector instanceof \ReflectionProperty)
    {
      if (self::GET === $prefix && $normalize)
      {
        $var = ReflectionVar::fromReflectionProperty($this->reflector);
        if ($var instanceof ReflectionNamedVar && $var->isBool())
        {
          $prefix = self::IS;
        }
      }

      if (self::SET === $prefix || self::HAS === $prefix || self::IS === $prefix || self::GET === $prefix)
      {
        return $prefix . $this->pascal();
      }

      throw new \InvalidArgumentException(("Invalid method prefix '$prefix' for given."));
    }

    throw new \LogicException('%s::method can only be used for ' . $this->string . '.');
  }

  /**
   * Converts this object into a PascalCase string. IE- Converts 'class_name' to 'ClassName'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector classify)
   * @return string The PascalCase string
   */
  public function pascal(): string
  {
    return str_replace(str_split(static::PATTERN_PASCAL), '', $this->ucwords());
  }

  /**
   * Converts this object name into a snake_case string. IE- Converts 'ClassName' to 'class_name'.
   *
   * @author Jonathan H. Wage <jonwage@gmail.com> (Borrowed from Doctrine Inflector tableize)
   * @return string
   */
  public function snake(): string
  {
    return strtolower(preg_replace(static::PATTERN_SNAKE, '_$1', $this->string));
  }

  /**
   * Converts this object to a string with capitalized words, separated by the given separators
   *
   * @param string $separators
   *
   * @return string
   */
  public function ucwords($separators = self::PATTERN_PASCAL): string
  {
    return ucwords($this->string, $separators);
  }

  // endregion ///////////////////////////////////////////// End String Methods

  // region //////////////////////////////////////////////// Exact Matches

  /**
   * @return bool
   */
  public function isClass()
  {
    return empty($this->member) && class_exists($this->string) && !(function_exists('enum_exists') && enum_exists($this->string));
  }

  /**
   * @return bool
   */
  public function isConstant(): bool
  {
    return static::SCOPE === $this->separator && self::DOLLAR !== $this->prefix && defined($this->string);
  }

  /**
   * @return bool
   */
  public function isEnum()
  {
    if (empty($this->member))
    {
      if (function_exists('enum_exists'))
      {
        return enum_exists($this->string);
      }

      if ($class = defined('POLYFILL_ENUM') ? constant('POLYFILL_ENUM') : null)
      {
        if (class_exists($class) && is_subclass_of($this->string, $class))
        {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * @return bool
   */
  public function isEnumCase(): bool
  {
    if (function_exists('enum_exists'))
    {
      return enum_exists($this->class) && $this->isConstant();
    }

    if ($class = defined('POLYFILL_ENUM') ? constant('POLYFILL_ENUM') : null)
    {
      if (class_exists($class) && is_subclass_of($this->string, $class))
      {
        return $this->isConstant();
      }
    }

    return false;
  }

  /**
   * @return bool
   */
  public function isInterface(): bool
  {
    return empty($this->member) && interface_exists($this->string);
  }

  /**
   * @return bool
   */
  public function isMethod(): bool
  {
    return method_exists($this->class, $this->member);
  }

  /**
   * @return bool
   */
  public function isNamespace()
  {
    if (0 === $this->is_namespace)
    {
      if (!empty($this->member) || $this->isClass())
      {
        $this->is_namespace = false;
      }
      else
      {
        $this->is_namespace = (new ReflectionProject())->getNamespaces()->has($this->string);
      }
    }

    return $this->is_namespace;
  }

  /**
   * @return bool
   */
  public function isProperty(): bool
  {
    return property_exists($this->class, $this->member);
  }

  /**
   * @return bool|null
   */
  public function isTrait()
  {
    if (0 === $this->is_trait)
    {
      $this->is_trait = null;
      if (!empty($this->member) || $this->isClass() || $this->isInterface())
      {
        return $this->is_trait = false;
      }

      $traits = get_declared_traits();
      if (in_array($this->string, $traits))
      {
        return $this->is_trait = true;
      }

      $Project  = new ReflectionProject();
      $relative = str_replace('\\', '/', $this->string) . '.php';
      if ($Project->hasFile($relative))
      {
        if ($this->fileHasTrait($Project->getDir().DIRECTORY_SEPARATOR.$relative, $this->string))
        {
          return $this->is_trait = true;
        }
      }
      else
      {
        $short = substr(strrchr($this->string, '\\'), 1);
        $ns    = str_replace($short . '\\', '', $this->string);

        $all = $Project->getNamespaces();
        if ($all->has($ns))
        {
          if ($dirs = $all->get($ns))
          {
            foreach($dirs as $dir)
            {
              foreach(glob($dir . DIRECTORY_SEPARATOR.  '*.php') as $file)
              {
                if ($this->fileHasTrait($file, $this->string))
                {
                  return $this->is_trait = true;
                }
              }
            }
          }
        }
      }
    }

    return $this->is_trait;
  }

  // endregion ///////////////////////////////////////////// End Exact Matches

  // region //////////////////////////////////////////////// Like Matches

  /**
   * @return bool
   */
  public function isConstantLike(): bool
  {
    if ($this->isConstant())
    {
      return true;
    }

    return static::SCOPE === $this->separator && self::DOLLAR !== $this->prefix && strtoupper($this->member) === $this->member;
  }

  /**
   * @return bool|null
   */
  public function isMethodLike()
  {
    if ($this->isMethod())
    {
      return true;
    }

    if ($this->isConstant() || $this->isProperty())
    {
      return false;
    }

    if (str_ends_with($this->suffix, ')') || self::OBJECT === $this->separator)
    {
      return true;
    }

    if (self::SCOPE === $this->separator)
    {
      if (self::DOLLAR === $this->prefix)
      {
        return false;
      }

      // Now we are guessing based on coding standards
      if (preg_match('#(get|set|is|has)#', $this->member))
      {
        return true;
      }

      if (strtoupper($this->member) === $this->member)
      {
        // Following general convention & PSR-2 recommendation (https://www.php-fig.org/psr/psr-2/)
        return false;
      }
    }

    // Ambiguous
    return null;
  }

  /**
   * @return bool
   */
  public function isPropertyLike(): bool
  {
    if ($this->isProperty())
    {
      return true;
    }

    if (static::SCOPE === $this->separator)
    {
      return static::DOLLAR === $this->prefix;
    }
    elseif (static::OBJECT === $this->separator)
    {
      return !$this->isMethod() && !str_ends_with($this->suffix, ')');
    }

    return false;
  }

  // endregion ///////////////////////////////////////////// End Like Matches

  // region //////////////////////////////////////////////// Helpers

  /**
   * @param $string
   *
   * @return string[]
   */
  protected function explodeClass($string): array
  {
    $short = substr(strrchr($string, '\\'), 1);

    return [substr($string, 0, -(strlen($short) + 1)), $short];
  }

  /**
   * @param string $file
   * @param string $trait
   *
   * @return bool
   */
  protected function fileHasTrait(string $file, string $trait): bool
  {
    if (is_file($file))
    {
      $code = file_get_contents($file);
      if ($found = $this->getTraitInCode($code))
      {
        if ($found !== $trait)
        {
          if (str_ends_with($trait, $found))
          {
            if ($ns = $this->getNamespaceInCode($code))
            {
              $found = $ns . '\\' . $found;
            }
          }
        }
      }
    }

    return isset($found) && $found === $trait;
  }

  /**
   * @param string $code
   *
   * @return string|null
   */
  protected function getTraitInCode(string $code)
  {
    $tokens = token_get_all($code);
    $count  = count($tokens);

    for ($i = 0; $i < $count; $i++)
    {
      if (is_array($tokens[$i]) && T_TRAIT === $tokens[$i][0])
      {
        for ($j = $i + 1; $j < $count; $j++)
        {
          if (T_STRING === $tokens[$j][0])
          {
            return $tokens[$j][1];
          }
        }
      }
    }

    return null;
  }

  /**
   * @param string $code
   *
   * @return string|null
   */
  protected function getNamespaceInCode(string $code)
  {
    $tokens            = token_get_all($code);
    $namespace         = '';
    $finding_namespace = false;

    foreach ($tokens as $token)
    {
      // Skip over simple one-character tokens that are not part of the namespace name
      if (is_string($token))
      {
        if ($finding_namespace && ';' === $token)
        {
          // End of namespace declaration
          $finding_namespace = false;

          break;
        }

        continue;
      }

      list($id, $text) = $token;

      switch ($id)
      {
        case T_NAMESPACE:
          $finding_namespace = true;

          break;
        case T_NS_SEPARATOR:
          if ($finding_namespace)
          {
            $namespace .= '\\';
          }

          break;
        case T_STRING:
          if ($finding_namespace)
          {
            $namespace .= $text;
          }

          break;
        case T_COMMENT:
        case T_DOC_COMMENT:
        case T_WHITESPACE:
          // Ignore comments and whitespace while building the namespace string
          break;
        default:
          // Stop if any other major token type is encountered
          if ($finding_namespace)
          {
            $finding_namespace = false;

            break 2; // Break out of both the switch and the foreach loop
          }

          break;
      }
    }

    return '' === $namespace ? null : $namespace;
  }

  // endregion ///////////////////////////////////////////// End Helpers
}
