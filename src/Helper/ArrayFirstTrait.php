<?php

namespace DevCoding\Reflection\Helper;

trait ArrayFirstTrait
{
  /**
   * Polyfill for the PHP 8.5 function of the same name.
   * @template TKey
   * @template TValue
   *
   * @param array<TKey, TValue> $array
   *
   * @return TValue|null
   *
   * @link https://github.com/symfony/polyfill/blob/1.x/src/Php85/Php85.php
   * @link https://www.php.net/manual/en/function.array-first.php
   */
  protected function array_first(array $array) { foreach ($array as $value) { return $value; } return null; }
}