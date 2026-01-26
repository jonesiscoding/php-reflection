<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface with patterns for shapes of PHP types that are commonly used in PHPdocs
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface Shapes
{
  const SHAPE = [
    'object{(.*)}$' => 'object',
    'array{(.*)}$'  => 'array',
  ];
}
