<?php

namespace DevCoding\Reflection\Vars;

/**
 * Reflection-style class representing a 'string' RelectionNamedVar with specific choices.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionEnumVar extends ReflectionNamedVar
{
  const PATTERN = '#enum-string<([^>]+)>#';

  /** @var array|null */
  protected $choices;

  public function __construct(\Reflector $reflector, array $choices = array(), string $description = '')
  {
    $this->choices = !empty($choices) ? $choices : null;

    parent::__construct($reflector, 'string', false, $description);
  }

  public static function handles($type): bool
  {
    $type = is_array($type) && count($type) === 1 ? reset($type) : $type;

    return is_string($type) && preg_match(self::PATTERN, $type);
  }

  /**
   * @return \Symfony\Component\Validator\Constraints\Choice|null
   */
  public function getAnnotation()
  {
    if (isset($this->choices))
    {
      if (class_exists('\\Symfony\\Component\\Validator\\Constraints\\Choice'))
      {
        return new \Symfony\Component\Validator\Constraints\Choice(array('choices' => $this->choices));
      }
    }

    return null;
  }
}
