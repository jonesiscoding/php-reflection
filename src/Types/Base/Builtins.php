<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface with constants for Builtin PHP types
 *
 * @see     https://www.php.net/manual/en/language.types.type-system.php
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface Builtins extends Scalar, Psuedo, Singleton
{
  const ARRAY    = 'array';
  const OBJECT   = 'object';
  const NULL     = 'null';
  const RESOURCE = 'resource';
  const CALLABLE = 'callable';
}
