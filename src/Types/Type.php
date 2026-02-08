<?php

namespace DevCoding\Reflection\Types;

use DevCoding\Reflection\ReflectionClassName;
use DevCoding\Reflection\Types\Base\Aliases;
use DevCoding\Reflection\Types\Base\Builtins;
use DevCoding\Reflection\Types\Base\CompoundInterface;
use DevCoding\Reflection\Types\Base\Iterables;
use DevCoding\Reflection\Types\Base\Psuedo;
use DevCoding\Reflection\Types\Base\Reference;
use DevCoding\Reflection\Types\Base\Scalar;
use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Base\Singleton;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Base\VariableInterface;
use DevCoding\Reflection\Types\Factory\Factory;
use DevCoding\Reflection\Types\Shape\ShapeDefinition;

/**
 * Object class representing a PHP type
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
abstract class Type implements Builtins, Aliases, Reference, TypeInterface
{
  /** @var Factory */
  protected static $factory;
  /** @var array */
  private static $types;
  /** @var string */
  protected $normalized;
  /** @var string */
  protected $raw;
  /** @var ShapeDefinition|null */
  protected $shape;
  /** @var string */
  protected $short;

  // region //////////////////////////////////////////////// Instantiation

  /**
   * Sets and normalizes the type. If the type is not a builtin or a fully qualified class/interface, and a context is
   * given, will attempt to resolve the type into a fully qualified class/interface.
   *
   * @param string $raw
   */
  protected function __construct(string $raw)
  {
    $this->raw = $raw;
  }

  /**
   * Instantiates a type from the given string and optional context, throwing an exception if the type cannot be
   * identified or resolved.
   *
   * @param string     $string    Type
   * @param \Reflector $context   Reflector to use when resolving class names to fully qualified class names
   * @param array{ shape: ShapeDefinition, type: string, inner: string } $matches;
   *
   * @return self
   * @throws \ReflectionException
   */
  public static function from(string $string, \Reflector $context = null, &$matches = []): Type
  {
    $matches = [];
    $factory = static::$factory = static::$factory ?? new Factory();
    $class   = $factory->match($string, $context, $matches);
    $type    = !empty($matches['type']) ? $matches['type'] : $string;
    $object  = new $class($type, $context);

    if ($object instanceof VariableInterface && isset($matches['type']))
    {
      $object->setType($matches['type']);
    }
    elseif ($object instanceof CompoundInterface)
    {
      $factory->inner($object, $matches, $context);
    }

    if ($object instanceof ShapeInterface)
    {
      $factory->shape($object, $matches, $context);
    }

    return $object;
  }

  public static function fromReflector(\Reflector $reflector): Type
  {
    $factory = static::$factory = static::$factory ?? new Factory();

    return static::from($factory->extract($reflector), $reflector);
  }

  /**
   * @param string          $string
   * @param \Reflector|null $context
   *
   * @return Type|null
   */
  public static function tryFrom(string $string, \Reflector $context = null)
  {
    try
    {
      return self::from($string, $context);
    }
    catch(\Throwable $t)
    {
      return null;
    }
  }

  public static function tryFromReflector(\Reflector $reflector)
  {
    try
    {
      return self::fromReflector($reflector);
    }
    catch(\Throwable $t)
    {
      return null;
    }
  }

  // endregion ///////////////////////////////////////////// End Instantiation

  // region //////////////////////////////////////////////// Static Public

  /**
   * Returns an array of all PHP built in types.
   *
   * @return string[]
   */
  public static function getBuiltin(): array
  {
    return static::getTypes(Builtins::class);
  }

  /**
   * Returns an array of PHP types that are considered psuedotypes.
   *
   * @return string[]
   */
  public static function getPsuedo(): array
  {
    return static::getTypes(Psuedo::class);
  }

  public static function getReference(): array
  {
    return static::getTypes(Reference::class);
  }

  /**
   * Returns an array of PHP types that are considered scalar.
   *
   * @return string[]
   */
  public function getScalar(): array
  {
    return static::getTypes(Scalar::class);
  }

  /**
   * Returns an array of PHP types that are considered singletons.
   *
   * @return string[]
   */
  public static function getSingleton(): array
  {
    return static::getTypes(Singleton::class);
  }

  /**
   * Returns an array of PHP types that are considered traversable.
   *
   * @return string[]
   */
  public static function getIterable(): array
  {
    return static::getTypes(Iterables::class);
  }

  // endregion ///////////////////////////////////////////// End Public Static

  // region //////////////////////////////////////////////// Public

  /**
   * @return string
   */
  public function __toString()
  {
    return $this->normalized ?? $this->raw;
  }

  public function allowsNull(): bool
  {
    return false;
  }

  /**
   * If this type is a class or interface, returns the short name.
   * If this type is an aliased or builtin type, returns the normalized type.
   *
   * @return string
   */
  public function getShortName(): string
  {
    if (!isset($this->short))
    {
      if ($this->isBuiltin())
      {
        $this->short = $this->normalized;
      }
      else
      {
        $this->short = (new ReflectionClassName($this->normalized))->getShortName();
      }
    }

    return $this->short;
  }

  /**
   * If this type is a class or interface, returns the namespace.
   * If this type is an alias or builtin, returns an empty string.
   *
   * @return string
   */
  public function getNamespace(): string
  {
    if (!isset($this->namespace))
    {
      if ($this->isBuiltin())
      {
        $this->namespace = '';
      }
      else
      {
        $this->namespace = (new ReflectionClassName($this->normalized))->getNamespace();
      }
    }

    return $this->namespace;
  }

  /**
   * Evaluates if the raw value has a namespace; the namespace is not validated to exist.
   *
   * @return bool
   */
  public function hasNamespace(): bool
  {
    return false !== strpos($this->raw, '\\');
  }

  /**
   * Evaluates if the normalized type matches a builtin PHP type.
   *
   * @return bool
   */
  public function isBuiltin(): bool
  {
    return array_key_exists($this->raw, $b = static::getBuiltin()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Evaluates if the normalized type is a class.
   *
   * @return bool
   */
  public function isClass(): bool
  {
    return class_exists($this->raw);
  }

  /**
   * Evaluates if the normalized type is an interface.
   *
   * @return bool
   */
  public function isInterface(): bool
  {
    return interface_exists($this->raw);
  }

  /**
   * Evaluates if the raw or normalized type is an iterable type.
   *
   * @return bool
   */
  public function isIterable(): bool
  {
    return array_key_exists($this->raw, $b = static::getIterable()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Evaluates if the raw or normalized type is a psuedotype.
   *
   * @return bool
   */
  public function isPsuedo(): bool
  {
    return array_key_exists($this->raw, $b = static::getPsuedo()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Evaluates if the raw or normalized type is a reference type.
   *
   * @return bool
   */
  public function isReference(): bool
  {
    return array_key_exists($this->raw, $b = static::getReference()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Evaluates if the raw or normalized type is a scalar type.
   *
   * @return bool
   */
  public function isScalar(): bool
  {
    return array_key_exists($this->raw, $b = static::getScalar()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Evaluates if the raw or normalized type is a singleton type.
   *
   * @return bool
   */
  public function isSingleton(): bool
  {
    return array_key_exists($this->raw, $b = static::getSingleton()) || array_key_exists($this->normalized, $b);
  }

  /**
   * Returns a clone of this object, using only the previously normalized type.
   *
   * @return Type
   */
  public function raw()
  {
    $obj      = clone $this;
    $obj->normalized = null;

    return $obj;
  }

  // endregion ///////////////////////////////////////////// End Public

  // region //////////////////////////////////////////////// Helpers

  /**
   * Retrieves an array of types from the type group string, which must be one of the existing interfaces.
   *
   * @param string $type
   *
   * @return string[]
   */
  private static function getTypes(string $type): array
  {
    if (interface_exists($type))
    {
      if (!isset(static::$types[$type]))
      {
        static::$types[$type] = array_flip((new \ReflectionClass($type))->getConstants());
      }

      return static::$types[$type];
    }

    throw new \InvalidArgumentException("Unknown Type Interface: $type");
  }

  /**
   * Evaluates if the raw type is lowercase.
   *
   * @return bool
   */
  protected function isLower(): bool
  {
    return $this->raw === strtolower($this->raw);
  }
}
