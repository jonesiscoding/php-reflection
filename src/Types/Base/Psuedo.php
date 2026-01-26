<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface with constants for PHP psuedotypes commonly used in PHPDoc blocks
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface Psuedo
{
  const MIXED    = 'mixed';
  const SCALAR   = 'scalar';
  const ITERABLE = 'iterable';
  const VOID     = 'void';
  const NEVER    = 'never';
}
