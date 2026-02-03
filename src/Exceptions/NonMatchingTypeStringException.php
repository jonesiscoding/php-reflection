<?php

namespace DevCoding\Reflection\Exceptions;

/**
 * Thrown when a string cannot be parsed or matched to a valid PHP type representation, such as invalid union syntax,
 * malformed array notation, unknown class, unknown types
 *
 * This exception extends {@see \ReflectionException} so callers already handling reflection-related
 * failures can catch a single base type.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class NonMatchingTypeStringException extends \ReflectionException
{
  /**
   * Creates an exception message composed of the given string, the non-matching type class (if provided) and an
   * optional extra message.
   *
   * @param string          $string   The type string that could not be matched/parsed
   * @param string|null     $class    Optional non-matching type class
   * @param string|null     $message  Optional extra detail to append to the message
   * @param int             $code     The exception code
   * @param \Throwable|null $previous Previous exception
   */
  public function __construct(string $string, string $class = null, string $message = null, $code = 0, \Throwable $previous = null)
  {
    $msg = sprintf('The given string "%s" is not a valid type', $string);
    $sfx = $class ? sprintf(' for "%s".', $class) : '.';

    parent::__construct(implode(' ', array_filter([$msg, $sfx, $message])), $code, $previous);
  }
}
