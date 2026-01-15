<?php

namespace DevCoding\Reflection\Bags;

/**
 * Containers for all constraints from a ReflectionMethod, ReflectionProperty, ReflectionClass, or ReflectionFunction.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ConstraintBag implements \Psr\Container\ContainerInterface
{
  /** @var \Symfony\Component\Validator\Constraint[] */
  protected $constraints;

  public function __construct(AnnotationBag $AnnotationBag)
  {
    $this->constraints = $AnnotationBag->constraints() ?? [];
  }

  /**
   * @param string $id
   *
   * @return \Symfony\Component\Validator\Constraint[]
   */
  public function get($id)
  {
    $constraints = [];
    foreach($this->constraints as $constraint)
    {
      if ($constraint instanceof $id)
      {
        $constraints[] = $constraint;
      }
    }

    return $constraints;
  }

  /**
   * @param mixed  $value
   * @param string $id
   *
   * @return void
   */
  public function validate($value, string $id = '')
  {
    $constraints = $this->constraints;
    if (!empty($id))
    {
      $constraints = $this->has($id) ? $this->get($id) : [];
    }

    foreach($constraints as $constraint)
    {
      $vClass    = $constraint->validatedBy();
      $Validator = new $vClass();
      if (is_a($Validator, '\\Symfony\\Component\\Validator\\ConstraintValidatorInterface'))
      {
        /** @var \Symfony\Component\Validator\ConstraintValidatorInterface $Validator */
        $Validator->validate($value, $constraint);
      }
    }
  }

  public function has($id)
  {
    foreach($this->constraints as $constraint)
    {
      if ($constraint instanceof $id)
      {
        return true;
      }
    }

    return false;
  }
}
