<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Vars\ReflectionVar;

/**
 * Reflection style class providing accessibility to a RefelctionProperty through a getter, setter, or directly.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class ReflectionAccess
{
  /** @var \ReflectionClass */
  protected $class;
  /** @var ReflectionName */
  protected $name;
  /** @var \ReflectionProperty */
  protected $property;
  /** @var \ReflectionMethod|null */
  protected $getter = false;
  /** @var \ReflectionMethod|null */
  protected $setter = false;
  /** @var bool */
  protected $writable;
  /** @var bool */
  protected $readable;
  /** @var bool */
  protected $nullable;
  /** @var bool */
  protected $defaultable;
  /** @var mixed */
  protected $default;

  /**
   * @param \ReflectionProperty   $property
   * @param \ReflectionClass|null $class
   */
  public function __construct(\ReflectionProperty $property, $class = null)
  {
    $this->property = $property;
    $this->class    = $class ?? $this->property->getDeclaringClass();
    $this->name     = new ReflectionName($property);
  }

  /**
   * @return \ReflectionClass
   */
  public function getClass(): \ReflectionClass
  {
    return $this->class;
  }

  /**
   * Returns the default value of a property. This method should be used after hasDefaultValue(). If the property does
   * not have a default value, an exception is thrown.
   *
   * @return mixed                  The default value
   * @throws \ReflectionException   If the property is not readable or does not have a default value
   */
  public function getDefaultValue()
  {
    if (!$this->isReadable())
    {
      throw new \ReflectionException(
        sprintf('%s->%s is not readable.', $this->class->getName(), $this->property->getName())
      );
    }

    if (!$this->isDefault())
    {
      throw new \ReflectionException(
        sprintf("%s->%s does not have a default value.", $this->class->getName(), $this->property->getName())
      );
    }

    return $this->default;
  }

  /**
   * @return \ReflectionMethod|null
   * @throws \ReflectionException
   */
  public function getGet()
  {
    if ($this->hasGet())
    {
      return $this->getter;
    }

    throw new \ReflectionException(
        sprintf('%s::%s() has not been implemented.', $this->class->getName(), $this->name->method())
    );
  }

  /**
   * @return \ReflectionProperty
   */
  public function getProperty(): \ReflectionProperty
  {
    return $this->property;
  }

  /**
   * @return \ReflectionMethod|null
   * @throws \ReflectionException
   */
  public function getSet()
  {
    if ($this->hasSet())
    {
      return $this->setter;
    }

    throw new \ReflectionException(sprintf(
        '%s::%s() has not been implemented.',
        $this->class->getName(),
        $this->name->method(ReflectionName::SET)
    ));
  }

  /**
   * Returns the value of the property in the context of the given object.  For properties with a getter, the getter
   * is invoked, otherwise ReflectionProperty->getValue is used.
   *
   * @param object $object         The object to retrieve the value from
   *
   * @return mixed                 The value
   * @throws \ReflectionException  If the property is not readable
   */
  public function getValue($object)
  {
    if ($this->isReadable())
    {
      if ($this->hasGet())
      {
        return $this->getGet()->invoke($object);
      }
      else
      {
        return $this->property->getValue($object);
      }
    }

    throw new \ReflectionException(
        sprintf('%s::%s is not readable', $this->class->getName(), $this->property->getName())
    );
  }

  /**
   * @param object $object         The object to retrieve the value from
   * @param mixed $value           The value to set
   *
   * @return $this
   * @throws \ReflectionException  If property is not writable, or a null value is given and property is not nullable.
   *
   */
  public function setValue($object, $value)
  {
    if ($this->isWritable())
    {
      if (null === $value && !$this->isNullable())
      {
        throw new \ReflectionException(
            sprintf('%s::%s is not nullable', $this->class->getName(), $this->property->getName())
        );
      }

      if ($this->hasSet())
      {
        $this->getSet()->invoke($object);
      }
      else
      {
        $this->property->setValue($object, $value);
      }

      return $this;
    }

    throw new \ReflectionException(
        sprintf('%s::%s is not writable', $this->class->getName(), $this->property->getName())
    );
  }

  /**
   * Evaluates if this object's ReflectionProperty has a getter method in the format of getPascalCase or isPascalCase
   *
   * @return bool
   */
  public function hasGet(): bool
  {
    if (false === $this->getter)
    {
      $this->getter = null;

      try
      {
        $name = $this->name->method();
        if ($this->class->hasMethod($name))
        {
          $method = $this->class->getMethod($name);
          if ($method->isPublic())
          {
            $this->getter = $method;
          }
        }
      }
      catch(\ReflectionException $e)
      {
        $this->getter = null;
      }
    }

    return isset($this->getter);
  }

  /**
   * Evaluates if this object's ReflectionProperty has a getter method in the format of setPascalCase
   *
   * @return bool
   */
  public function hasSet(): bool
  {
    if (!isset($this->setter))
    {
      $this->setter = null;

      try
      {
        $name = $this->name->method(ReflectionName::SET);
        if ($this->class->hasMethod($name))
        {
          $method = $this->class->getMethod($name);
          if ($method->isPublic())
          {
            $this->getter = $method;
          }
        }
      }
      catch(\ReflectionException $e)
      {
        $this->setter = null;
      }
    }

    return isset($this->setter);
  }

  /**
   * Evaluates if this object's ReflectionProperty has a default value.
   * @return true
   */
  public function isDefault()
  {
    if (!isset($this->defaultable))
    {
      if (method_exists($this->property, 'hasDefaultValue') && method_exists($this->property, 'getDefaultValue'))
      {
        if ($this->defaultable = $this->property->hasDefaultValue())
        {
          $this->default = $this->property->getDefaultValue();
        }
      }

      $name     = $this->property->getName();
      $defaults = $this->class->getDefaultProperties();

      if ($this->defaultable = array_key_exists($name, $defaults))
      {
        $this->default = $defaults[$name];
      }
    }

    return $this->defaultable;
  }

  /**
   * Evaluates if this object's ReflectionProperty is readable by establishing if it is either public or has a getter.
   *
   * @return bool
   */
  public function isReadable(): bool
  {
    if (!isset($this->readable))
    {
      $this->readable = $this->property->isPublic() || $this->hasGet();
    }

    return $this->readable;
  }

  /**
   * Evaluates if this object's ReflectionProperty is nullable
   *
   * @return bool
   */
  public function isNullable()
  {
    if (!isset($this->nullable))
    {
      $this->nullable = ReflectionVar::fromReflectionProperty($this->property)->allowsNull();
    }

    return $this->nullable;
  }

  /**
   * Evaluates if this object's ReflectionProperty is writable; property must not be read-only, and must be public or
   * have a standard setter method.
   *
   * @return bool
   */
  public function isWritable(): bool
  {
    if (!isset($this->writable))
    {
      if (method_exists($this->property, 'isReadOnly') && $this->property->isReadOnly())
      {
        $this->writable = false;
      }
      else
      {
        $this->writable = $this->property->isPublic() || $this->hasSet();
      }

      $var = ReflectionVar::fromReflectionProperty($this->property);
      if ($var->allowsNull())
      {
        $this->nullable = true;
      }
    }

    return $this->writable;
  }
}
