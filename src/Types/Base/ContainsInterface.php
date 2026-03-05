<?php

namespace DevCoding\Reflection\Types\Base;

/**
 * Interface that all Type classes which may contain another type must implment.
 */
interface ContainsInterface
{
  /**
   * MUST Evaluate if this type contains the given type.
   *
   * @param TypeInterface $find
   * @return bool
   */
  public function contains(TypeInterface $find): bool;

  /**
   * MUST replace the 'find' type with the 'replace' type
   *
   * @param TypeInterface $find
   * @param TypeInterface $repl
   * @return $this
   */
  public function replace(TypeInterface $find, TypeInterface $repl);
}
