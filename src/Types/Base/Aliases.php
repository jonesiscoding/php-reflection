<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface for aliases for PHP types that are commonly used in PHPdocs
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @see     https://docs.phpdoc.org/guide/guides/types.html
 * @see     https://phpstan.org/writing-php-code/phpdoc-types
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
interface Aliases
{
  const ALIAS = [
    'bool(ean)?'                         => 'bool',
    'int(eger)?'                         => 'int',
    'non\-(positive|negative|zero)\int'  => 'int',
    'number'                             => 'float',
    'numeric'                            => 'float',
    'real'                               => 'float',
    'double'                             => 'float',
    'array-key'                          => 'string',
    'list'                               => 'array',
    '(literal|non\-falsy|truthy)-string' => 'string',
    'non-empty-(array|list)'             => 'array',
    '(closed|open)\-resource'            => 'resource',
    'pure\-callable'                     => 'callable',
    'callable\-(array|string)'           => 'callable',
    'callable\-array'                    => 'callable',
    'never(\-return[s])'                 => 'never',
    'no\-return'                         => 'never',
  ];
}
