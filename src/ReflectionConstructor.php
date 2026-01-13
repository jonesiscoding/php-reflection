<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Exceptions\MissingParameterException;
use DevCoding\Reflection\Tags\ReflectionTag;
use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection-style class representing a class constructor, for use in the autowiring of class objects.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionConstructor extends ReflectionTag
{
  /** @var \ReflectionMethod */
  protected $constructor;
  protected $class;

  /**
   * @param \ReflectionMethod $constructor
   * @param \ReflectionClass|null $class
   */
  public function __construct(\ReflectionMethod $constructor, $class = null)
  {
    $this->constructor = $constructor;
    $this->class       = $class;

    $tag = ReflectionTag::extract($constructor->getDocComment(), null, ReflectionTag::METHOD);

    parent::__construct($this->constructor, $tag);
  }

  /**
   * @param \ReflectionClass $ReflectionClass
   *
   * @return static|null
   */
  public static function fromReflectionClass(\ReflectionClass $ReflectionClass)
  {
    if ($constructor = $ReflectionClass->getConstructor())
    {
      return new static($constructor, $ReflectionClass);
    }

    return null;
  }

  /**
   * @param string $name
   * @param array  $arguments
   *
   * @return $this|mixed
   */
  public function __call(string $name, array $arguments)
  {
    if (method_exists($this->constructor, $name))
    {
      $result = call_user_func_array([$this->constructor, $name], $arguments);

      if ($result instanceof \ReflectionMethod && $this->constructor === $result)
      {
        return $this;
      }
      else
      {
        return $result;
      }
    }

    throw new \LogicException('Call to undefined method ' . static::class . '::' . $name . '()');
  }

  /**
   * Matches a value in the given array to the given parameter. Parameter names can be matched via their original form,
   * snake_case, or PascalCase.  Values must match the type required by the parameter.
   *
   * @param \ReflectionParameter $parameter   Parameter to match
   * @param array                $array       Associative array with argument names as keys.
   *
   * @return mixed
   * @throws \InvalidArgumentException        If the parameter cannot be matched
   */
  public function getMatch(\ReflectionParameter $parameter, array $array)
  {
    $name  = new ReflectionName($parameter);
    $type  = $this->getParameterType($parameter->getName());
    $tries = [$name, $name->snake(), $name->pascal()];

    foreach($tries as $try)
    {
      if (array_key_exists($try, $array))
      {
        $possible = $array[$try];
        $possType = is_object($possible) ? get_class($possible) : gettype($possible);
        if ($possType === (string) $type)
        {
          return $possible;
        }
      }
    }

    throw new \InvalidArgumentException('$tries');
  }

  /**
   * Parses the given array matching names and types to constructor arguments, and returns an indexed array in the
   * correct order for instantiation of this object's constructor.
   *
   * @param array $array               Associative array with argument names as keys.
   *
   * @return array                     Indexed array that can be used for instantiation
   * @throws MissingParameterException If a required parameter cannot be matched to a value in the given array
   */
  public function getMatches(array $array)
  {
    $args    = [];
    $matched = [];
    foreach($this->constructor->getParameters() as $parameter)
    {
      // Initialize variable just to keep linter from complaining
      $arg  = null;
      // Get the name for later use
      $name = $parameter->getName();
      // Start with negative match
      $matched[$name] = false;

      try
      {
        // This will throw if a match isn't found
        $arg = $this->getMatch($parameter, $array);

        // Match was found
        $matched[$name] = true;
      }
      catch(\InvalidArgumentException $e)
      {
        // No match was found, try default value
        if ($parameter->isDefaultValueAvailable())
        {
          $arg = $parameter->getDefaultValue();

          // Match was found
          $matched[$name] = true;
        }
      }

      if (true === $matched[$name])
      {
        // Add to array of arguments
        $args[] = $arg;
      }
    }

    $missing = array_keys(array_filter($matched, function($m) { return $m === false; }));
    if (!empty($missing))
    {
      throw new MissingParameterException($missing);
    }

    return $args;
  }

  /**
   * @param string $name
   *
   * @return ReflectionVar|null
   */
  public function getParameterType(string $name)
  {
    foreach($this->params as $param)
    {
      if ($param->name === $name)
      {
        return $param->type;
      }
    }

    return null;
  }

  /**
   * Evaluates if the constructor has any required arguments
   *
   * @return bool
   */
  public function isOptional(): bool
  {
    return 0 === $this->constructor->getNumberOfRequiredParameters();
  }

  /**
   * Evaluates if the constructor uses a single array argument, rather than individual parameters.
   *
   * @return bool
   */
  public function isArray(): bool
  {
    if (1 === $this->constructor->getNumberOfRequiredParameters())
    {
      if ($param = $this->params[0] ?? null)
      {
        if ('array' === $param->type)
        {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Instantiates the object using the given associative array.  Keys are first matched to parameter names, placing
   * the arguments in the correct order for the constructor.
   *
   * @param array $args             Associative array with argument names as keys.
   *
   * @return object|null
   * @throws \ReflectionException
   */
  public function newInstanceArgs(array $args)
  {
    if ($this->isArray())
    {
      return $this->class->newInstance($args);
    }
    else
    {
      return $this->class->newInstanceArgs($this->getMatches($args));
    }
  }
}
