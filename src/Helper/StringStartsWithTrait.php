<?php

namespace DevCoding\Reflection\Helper;

trait StringStartsWithTrait
{
  /**
   * Polyfill for PHP8's str_starts_with.
   *
   * @param string $haystack
   * @param string $needle
   *
   * @return bool
   */
  protected function str_starts_with(string $haystack, string $needle): bool
  {
    if (function_exists('str_starts_with'))
    {
      return str_starts_with($haystack, $needle);
    }

    return 0 === strncmp($haystack, $needle, \strlen($needle));
  }
}
