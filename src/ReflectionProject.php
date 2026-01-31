<?php

namespace DevCoding\Reflection;

use DevCoding\Reflection\Bags\ClassBag;
use DevCoding\Reflection\Bags\NamespaceBag;

/**
 * Reflection style class representing a Composer project.
 *
 * @author  AMJones <am@jonesiscoding.com>
 * @license https://github.com/jonesiscoding/php-reflection/blob/main/LICENSE
 */
class ReflectionProject
{
  /** @var NamespaceBag */
  protected $namespaces;
  /** @var string */
  protected $dir;
  /** @var array<string, string> */
  protected $autoload;
  /** @var ClassBag */
  protected $classes;

  /**
   * Evaluates if a file exists at the path given, relative to the project root.
   *
   * @param string $file
   *
   * @return bool
   */
  public function hasFile(string $file): bool
  {
    return is_file($this->getDir() . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR));
  }

  /**
   * Returns an array of directory => namespace
   *
   * @return array
   */
  public function getAutoload()
  {
    if (!isset($this->autoload))
    {
      try
      {
        if ($projectDir = $this->getDir())
        {

          $composer       = $projectDir.'/vendor/composer';
          $autoloaders    = [$composer.'/autoload_psr4.php', $composer.'/autoload_namespaces.php'];
          $this->autoload = [];

          foreach ($autoloaders as $autoloader)
          {
            if (file_exists($autoloader))
            {
              $autoload = include_once $autoloader;
              foreach ($autoload as $ns => $dirs)
              {
                foreach ($dirs as $dir)
                {
                  $this->autoload[$dir] = rtrim($ns, "\\");
                }
              }
            }
          }

          foreach ($this->autoload as $dir => $ns)
          {
            if (is_dir($dir))
            {
              $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
              foreach ($rii as $file)
              {
                if ($file->isDir())
                {
                  $path  = rtrim($file->getPathname(), "/.");
                  $subNs = preg_replace("#^".$dir."(.*)$#", '$1', $path);
                  $subNs = str_replace("/", "\\", $subNs);
                  if (!empty($subNs))
                  {
                    $this->autoload[$path] = $ns.$subNs;
                  }
                }
              }
            }
          }
        }
      }
      catch (\Exception $e)
      {
        $this->autoload = [];
      }
    }

    return $this->autoload;
  }

  /**
   * Returns a ClassBag of all classes known to composer or previously loaded.
   *
   * @return ClassBag
   */
  public function getClasses(): ClassBag
  {
    return $this->populate()->classes;
  }

  /**
   * Determines the location of the project root directory by locating the composer.json.  Any composer.json files in
   * the 'vendor' directory are ignored, allowing this method to be used within a composer installed library.
   *
   * @return string
   */
  public function getDir()
  {
    if (null === $this->dir)
    {
      $r = new \ReflectionObject($this);

      if (!file_exists($dir = $r->getFileName()))
      {
        throw new \LogicException('Cannot auto-detect project directory.');
      }

      $dir = $rootDir = \dirname($dir);
      while (false !== strpos($dir, 'vendor') || !file_exists($dir.'/composer.json'))
      {
        if ($dir === \dirname($dir))
        {
          return $this->dir = $rootDir;
        }
        $dir = \dirname($dir);
      }

      $this->dir = $dir;
    }

    return $this->dir;
  }

  /**
   * Returns a NamespaceBag with all namespaces of classes known to composer or previously loaded.
   *
   * @return NamespaceBag
   */
  public function getNamespaces(): NamespaceBag
  {
    return $this->populate()->namespaces;
  }

  /**
   * Returns an array of all known classes
   *
   * @return $this
   */
  private function populate()
  {
    if (!isset($this->classes))
    {
      $this->classes    = new ClassBag();
      $this->namespaces = new NamespaceBag();
      if ($projectDir = $this->getDir())
      {
        try
        {
          // Get the Composer autoloader instance
          $ClassLoader = require $projectDir . '/vendor/autoload.php';

          // Retrieve the class map (an associative array of class names and file paths)
          $this->classes->merge($ClassLoader->getClassMap());

          $groups = [get_declared_classes(), get_declared_interfaces(), get_declared_traits()];
          foreach($groups as $group)
          {
            foreach($group as $class)
            {
              if (!$this->classes->has($class))
              {
                $this->classes->offsetSet($class, false);
              }
            }
          }

          $namespaces = [];
          foreach($this->classes as $class => $file)
          {
            $ns = array_first(ReflectionString::explodeClass($class));

            if (!isset($namespaces[$ns]))
            {
              $namespaces[$ns] = new ClassBag();
            }

            $namespaces[$ns]->offsetSet($class, $file);
          }

          $this->namespaces->merge($namespaces);
        }
        catch(\Throwable $t)
        {
        }
      }
    }

    return $this;
  }
}
