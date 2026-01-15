<?php

namespace DevCoding\Reflection\Exceptions;

/**
 * Exception class used to indicate that a required parameter/property is missing from an array. This typically is
 * thrown when an ObjectArrayValue object cannot be autowired.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class MissingParameterException extends \InvalidArgumentException
{
  public function __construct($parameters, $code = 0, \Exception $previous = null)
  {
    $msg = [];
    foreach($parameters as $name => $type)
    {
      $msg[] = sprintf('%s $%s', $type, $name);
    }

    parent::__construct('The following parameters are missing: '.implode(', ', $msg), $code, $previous);
  }
}