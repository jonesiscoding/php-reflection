<?php

namespace DevCoding\Reflection;

/**
 * Reflection-style class for access to an annotation of a ReflectionClass, ReflectionProperty, or ReflectionMethod
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionAnnotation
{
  /** @var $ReflectionProperty */
  protected $Reflector;
  /** @var object */
  protected $annotation;

  /**
   * @param \ReflectionProperty|\ReflectionMethod|\ReflectionClass $Reflector  Relfector with the annotation
   * @param object                                                 $annotation Annotation Object
   */
  public function __construct(\Reflector $Reflector, $annotation)
  {
    $this->Reflector  = $Reflector;
    $this->annotation = $annotation;
  }

  /**
   * Returns the Reflector for the property, method, or class that the annotation references.
   *
   * @return \ReflectionProperty|\ReflectionMethod|\ReflectionClass
   */
  public function getReflector(): \Reflector
  {
    return $this->Reflector;
  }

  /**
   * Returns the annotation object
   *
   * @return object
   */
  public function getAnnotation()
  {
    return $this->annotation;
  }

  /**
   * Evaluates whether an annotation is present
   *
   * @return bool
   */
  public function hasAnnotation(): bool
  {
    return isset($this->annotation);
  }
}
