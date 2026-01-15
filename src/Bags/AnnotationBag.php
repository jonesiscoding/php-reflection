<?php

namespace DevCoding\Reflection\Bags;

use DevCoding\Reflection\Doctrine;
use DevCoding\Reflection\Helper\ArrayFirstTrait;
use DevCoding\Reflection\ReflectionAnnotation;
use Psr\Container\ContainerInterface;

/**
 * Containers for all annotations from a ReflectionMethod, ReflectionProperty, ReflectionClass, or ReflectionFunction.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-relfection/blob/main/LICENSE
 */
class AnnotationBag implements ContainerInterface
{
  use ArrayFirstTrait;

  /** @var ReflectionAnnotation[] */
  protected $annotations;

  /**
   * Uses Doctrine's AnnotationReader to read the annotations from the given Reflector, and adds a ReflectionAnnotation
   * object for each.
   *
   * @param \ReflectionFunctionAbstract|\ReflectionProperty|\ReflectionClass $reflector
   * @param Doctrine\Common\Annotations\AnnotationReader                     $reader
   */
  public function __construct(\Reflector $reflector, $reader = null)
  {
    $reader = $reader ?? new Doctrine\Common\Annotations\AnnotationReader();

    if ($reflector instanceof \ReflectionClass)
    {
      $annos = $reader->getClassAnnotations($reflector);
    }
    elseif ($reflector instanceof \ReflectionMethod)
    {
      $annos = $reader->getMethodAnnotations($reflector);
    }
    elseif ($reflector instanceof \ReflectionProperty)
    {
      $annos = $reader->getPropertyAnnotations($reflector);
    }
    elseif($reflector instanceof \ReflectionFunction)
    {
      $annos = $reader->getFunctionAnnotations($reflector);
    }
    else
    {
      $annos = [];
    }

    foreach($annos as $anno)
    {
      $this->annotations[] = new ReflectionAnnotation($reflector, $anno);
    }
  }

  /**
   * Returns an array of ReflectionAnnotation objects with an annotation class that matches the given ID.
   *
   * @param string $id Fully Qualified Class Name
   *
   * @return ReflectionAnnotation[]
   */
  public function get($id)
  {
    $annotations = [];
    foreach($this->annotations as $annotation)
    {
      if ($annotation->getAnnotation() instanceof $id)
      {
        $annotations[] = $annotation;
      }
    }

    return $annotations;
  }

  /**
   * Returns a Column annotation, if present in this object.
   *
   * @return \Doctrine\ORM\Mapping\Column|null
   */
  public function column()
  {
    $column = '\\Doctrine\\ORM\\Mapping\\Column';
    if (class_exists($column))
    {
      if ($this->has($column))
      {
        return $this->array_first($this->get($column))->getAnnotation();
      }
    }

    return null;
  }

  /**
   * Returns all Constraint annotations that are present in this object
   *
   * @return \Symfony\Component\Validator\Constraint[]|null
   */
  public function constraints()
  {
    $constraint = '\\Symfony\\Component\\Validator\\Constraint';
    if (class_exists($constraint))
    {
      if ($this->has($constraint))
      {
        /** @var \Symfony\Component\Validator\Constraint[] */
        return $this->get($constraint);
      }
    }

    return null;
  }

  /**
   * Evaluates if this container has any annotations that match the given $id.
   *
   * @param string $id  Fully Qualified Class Name (of an annotation class)
   *
   * @return bool
   */
  public function has($id)
  {
    foreach($this->annotations as $annotation)
    {
      if ($annotation instanceof $id)
      {
        return true;
      }
    }

    return false;
  }
}
