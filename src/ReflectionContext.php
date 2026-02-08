<?php

namespace DevCoding\Reflection;

/**
 * @method static string export(mixed $argument, bool $return = false)
 * @method array getAttributes(?string $name = null, int $flags = 0)
 * @method mixed getConstant(string $name)
 * @method array getConstants(?int $filter = null)
 * @method ReflectionMethod|null getConstructor()
 * @method array getDefaultProperties()
 * @method string|false getDocComment()
 * @method int|false getEndLine()
 * @method ReflectionExtension|null getExtension()
 * @method string|false getExtensionName()
 * @method string|false getFileName()
 * @method array getInterfaceNames()
 * @method array getInterfaces()
 * @method ReflectionMethod getMethod(string $name)
 * @method ReflectionMethod[] getMethods(?int $filter = null)
 * @method int getModifiers()
 * @method string getName()
 * @method string getNamespaceName()
 * @method ReflectionClass|false getParentClass()
 * @method ReflectionProperty[] getProperties(?int $filter = null)
 * @method ReflectionProperty getProperty(string $name)
 * @method ReflectionClassConstant|false getReflectionConstant(string $name)
 * @method ReflectionClassConstant[] getReflectionConstants(?int $filter = null)
 * @method string getShortName()
 * @method int|false getStartLine()
 * @method array getStaticProperties()
 * @method mixed getStaticPropertyValue(string $name, mixed &$def_value = null)
 * @method array getTraitAliases()
 * @method array getTraitNames()
 * @method ReflectionClass[] getTraits()
 * @method bool hasConstant(string $name)
 * @method bool hasMethod(string $name)
 * @method bool hasProperty(string $name)
 * @method bool implementsInterface(string $interface)
 * @method bool inNamespace()
 * @method bool isAbstract()
 * @method bool isAnonymous()
 * @method bool isCloneable()
 * @method bool isEnum()
 * @method bool isFinal()
 * @method bool isInstance(object $object)
 * @method bool isInstantiable()
 * @method bool isInterface()
 * @method bool isInternal()
 * @method bool isIterable()
 * @method bool isIterateable()
 * @method bool isReadOnly()
 * @method bool isSubclassOf(string $class)
 * @method bool isTrait()
 * @method bool isUserDefined()
 * @method object newInstance(mixed ...$args)
 * @method object newInstanceArgs(array $args = [])
 * @method object newInstanceWithoutConstructor()
 * @method void setStaticPropertyValue(string $name, mixed $value)
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionContext
{
  /** @var \ReflectionClass  */
  protected $context;

  /**
   * @param \Reflector $context
   *
   * @throws \ReflectionException
   */
  final protected function __construct(\Reflector $context)
  {
    if ($context instanceof \ReflectionClass)
    {
      $this->context = $context;
    }
    elseif (method_exists($context, 'getDeclaringClass'))
    {
      $this->context = $context->getDeclaringClass();
    }
    elseif ($context instanceof \ReflectionFunction)
    {
      $this->context = $context->getClosureScopeClass();
    }
    elseif($context instanceof ReflectionString)
    {
      $this->context = new \ReflectionClass($context);
    }
    else
    {
      throw new \InvalidArgumentException('The context class could not be determined from the given Reflector.');
    }
  }

  /**
   * @return \ReflectionClass
   */
  public function getClass(): \ReflectionClass
  {
    return $this->context;
  }

  /**
   * Resolves the given alias into a fully qualified name using this context.
   *
   * @param string $alias
   *
   * @return string
   */
  public function resolve(string $alias): string
  {
    $prefixed = $this->getNamespaceName() . '\\' . $alias;
    if (class_exists('\\'.$prefixed))
    {
      return $prefixed;
    }
    else
    {
      $imports = new ReflectionClassImports($this->context);
      if ($imports->offsetExists($alias))
      {
        return $imports->offsetGet($alias);
      }
    }

    throw new \RuntimeException('The alias "'.$alias.'" could not be resolved into a fully qualified name in the context of "'.$this->getName().'"');
  }

  /**
   * @param string $alias
   *
   * @return string|null
   */
  public function tryResolve(string $alias)
  {
    try
    {
      return $this->resolve($alias);
    }
    catch(\Throwable $e)
    {
      return null;
    }
  }

  public function __toString()
  {
    return $this->context->__toString();
  }

  /**
   * @param string $name
   * @param array $arguments
   *
   * @return mixed|void
   */
  public function __call($name, $arguments)
  {
    if (method_exists($this->context, $name))
    {
      return $this->context->$name(...$arguments);
    }

    return $this->$name(...$arguments);
  }

  /**
   * @param \Reflector $context
   *
   * @return ReflectionContext
   * @throws \ReflectionException
   */
  public static function from(\Reflector $context): ReflectionContext
  {
    return new static($context);
  }

  /**
   * @param \Reflector $context
   *
   * @return ReflectionContext|null
   */
  public static function tryFrom(\Reflector $context)
  {
    try
    {
      return static::from($context);
    }
    catch(\ReflectionException $e)
    {
      return null;
    }
  }
}
