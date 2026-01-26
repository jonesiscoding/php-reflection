<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface with constants for PHP class references commonly used in PHPDoc blocks
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface Reference
{
  const SELF   = 'self';
  const STATIC = 'static';
  const THIS   = '$this';
}
