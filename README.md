# php-reflection

Library that adds additional Reflection-style classes, with additional functionality for PHP 7.0

## ReflectionComment Classes

Parses the contents of a comment retrieved from a function, property or class.  Each source type has a specific class
which can be used to parse and retrieve individual tags from the PHPdoc comment.

Tags are returned as a `ReflectionTag`.  In cases where tags may repeat such as `@property`, `@method` or `@param`, a
`TagBag` is returned.

## Type Classes

Similar to ReflectionType, this class provides information on a type, such as a property type, parameter type, or method 
return type. Unlike ReflectionType, the data for a Type can be pulled from the DocComment of the related
reflection object.


## ReflectionTag Classes

These classes are used internally to represent a tag from a `ReflectionComment`.

## Other Classes

### ReflectionClassImports

This class parases the `use` statements above the class declaration in a class, in order to resolve types referred to
by short names in PHPdoc comments.

### ReflectionConstructor

This class provides insight into a class constructor, allowing for instantiation via an array of named arguments.

### ReflectionName

This class provides manipulation of class, method, and property names, allowing for easy generation of method names
from property names, and vice versa.

### ReflectionAccess

This class provides basic information about wether a property is accessible within a class, whether directly or through
a getter and/or setter.
