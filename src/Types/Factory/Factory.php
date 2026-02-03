<?php

namespace DevCoding\Reflection\Types\Factory;

use DevCoding\Reflection\Exceptions\NonMatchingTypeStringException;
use DevCoding\Reflection\Exceptions\WithoutTypeReflectionException;
use DevCoding\Reflection\ReflectionContext;
use DevCoding\Reflection\Types\ArrayType;
use DevCoding\Reflection\Types\Base\Aliases;
use DevCoding\Reflection\Types\Base\CompoundInterface;
use DevCoding\Reflection\Types\Base\Reference;
use DevCoding\Reflection\Types\Base\ShapeInterface;
use DevCoding\Reflection\Types\Base\TypeInterface;
use DevCoding\Reflection\Types\Builtin;
use DevCoding\Reflection\Types\IntType;
use DevCoding\Reflection\Types\Nullable;
use DevCoding\Reflection\Types\Prototype;
use DevCoding\Reflection\Types\Shape\ShapeDefinition;
use DevCoding\Reflection\Types\StringType;
use DevCoding\Reflection\Types\Type;
use DevCoding\Reflection\Types\Union;
use DevCoding\Reflection\Types\WithoutTypeReflectionExecption;

class Factory
{
  const TYPE  = 'type';
  const SHAPE = 'shape';
  const INNER = 'inner';

  /** @var class-string<TypeInterface|CompoundInterface>[] */
  public $types;

  /**
   * @param array $types
   * @param array $shapes
   */
  public function __construct(array $types = array(), array $shapes = [])
  {
    $this->types = [
        Union::class,
        Nullable::class,
        Prototype::class,
        Object::class,
        ArrayType::class,
        StringType::class,
        IntType::class,
        Builtin::class
    ];

    foreach($types as $class)
    {
      if (is_subclass_of($class, TypeInterface::class))
      {
        $this->types[] = $class;
      }
    }

    foreach($shapes as $typeClass => $shapeClass)
    {
      if (is_subclass_of($typeClass, ShapeInterface::class))
      {
        $typeClass::addShapeDefinition($shapeClass);
      }
    }
  }

  /**
   * @param CompoundInterface  $object
   * @param array           $data
   * @param \Reflector|null $context
   */
  public function inner(CompoundInterface $object, array $data, \Reflector $context = null)
  {
    if (!isset($data['inner']))
    {
      throw new NonMatchingTypeStringException((string) $object->raw());
    }

    $object->setInner(Type::from($data['inner'], $context));
  }

  /**
   * @param string          $string
   * @param \Reflector|null $context
   * @param array           $matches
   * @return string
   * @throws NonMatchingTypeStringException
   */
  public function match(string &$string, \Reflector $context = null, array &$matches = []): string
  {
    $string  = $this->normalize($string);
    $matches = $matches ?? new Match();
    foreach($this->types as $type)
    {
      /** @var TypeInterface|CompoundInterface $type */
      if ($type::match($string, $context, $matches))
      {
        return $type;
      }
    }

    throw new NonMatchingTypeStringException($string);
  }

  /**
   * @param ShapeInterface  $object
   * @param array           $data
   * @param \Reflector|null $context
   */
  public function shape(ShapeInterface $object, array $data, \Reflector $context = null)
  {
    $sCls  = $data['shape'] ?? null;
    $inner = $data['inner'] ?? null;
    if ($inner && $sCls && is_subclass_of($sCls, ShapeDefinition::class))
    {
      $shape = array();
      if ($sCls::match($inner, $context, $shape))
      {
        $object->setShape(new $sCls($shape, $context));
      }
    }
  }

  protected function normalize(string $string, \Reflector $reference = null)
  {
    $normalized = $string;
    if (!str_contains($string, '|') && str_starts_with($string, '\\'))
    {
      // Trim leading backslash from non-union type
      $normalized = substr($string, 1);
    }

    // Resolve References
    $parts = explode('|', $normalized);
    foreach($parts as $index => $part)
    {
      if (in_array($normalized, [Reference::SELF, Reference::STATIC, Reference::THIS]))
      {
        $parts[$index] = ReflectionContext::from($reference)->getClass();
      }
    }
    $normalized = implode('|', $parts);

    if (!str_contains($string, '|') && !str_ends_with($string, '[]'))
    {
      if (class_exists($normalized) || interface_exists($normalized) || trait_exists($normalized))
      {
        return $normalized;
      }
      elseif (str_contains($string, '\\'))
      {
        // Resolve Unknown Classes
        return ReflectionContext::from($reference)->resolve($normalized);
      }

      foreach(Aliases::ALIAS as $pattern => $type)
      {
        if (preg_match('#' . $pattern . '#', $normalized))
        {
          return $type;
        }
      }
    }

    return $normalized;
  }

  public function extract(\Reflector $reflector)
  {
    if ($reflector instanceof \ReflectionFunctionAbstract)
    {
      return $this->fromReflectionFunction($reflector);
    }
    elseif ($reflector instanceof \ReflectionParameter)
    {
      return $this->fromReflectionParameter($reflector);
    }
    elseif ($reflector instanceof \ReflectionProperty)
    {
      return $this->fromReflectionProperty($reflector);
    }
    elseif ($reflector instanceof \ReflectionType)
    {
      return $this->fromReflectionType($reflector);
    }
    elseif ($reflector instanceof \ReflectionClass)
    {
      return $reflector->getName();
    }

    throw new WithoutTypeReflectionException($reflector);
  }

  protected function fromReflectionFunction(\ReflectionFunctionAbstract $function)
  {
    if ($type = $function->hasReturnType() ? $function->getReturnType() : null)
    {
      return $this->fromReflectionType($type, $function);
    }

    throw new WithoutTypeReflectionExecption($function);
  }

  protected function fromReflectionParameter(\ReflectionParameter $parameter)
  {
    if ($type = $parameter->hasType() ? $parameter->getType() : null)
    {
      return $this->fromReflectionType($type, $parameter);
    }

    throw new WithoutTypeReflectionExecption($parameter);
  }

  protected function fromReflectionProperty(\ReflectionProperty $property)
  {
    if (method_exists($property, 'hasType') && $property->hasType())
    {
      if ($type = method_exists($property, 'getType') ? $property->getType() : null)
      {
        return $this->fromReflectionType($type, $property);
      }
    }

    if (!method_exists($property, 'hasType'))
    {
      throw new \LogicException('Properties cannot be typed until PHP 7.4');
    }

    throw new WithoutTypeReflectionExecption($property);
  }

  /**
   * @param \ReflectionType $type
   * @param \Reflector|null $context
   *
   * @return string
   * @throws \ReflectionException
   */
  protected function fromReflectionType(\ReflectionType $type, \Reflector $context = null)
  {
    $parts = [];
    $types = method_exists($type, 'getTypes') ? $type->getTypes() : [];
    foreach($types as $type)
    {
      $parts[] = method_exists($type, 'getName') ? $type->getName() : (string) $type;
    }

    return implode('|', $parts);
  }
}
