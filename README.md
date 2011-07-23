Open Power Autoloader 3.0.3.0
=============================

This is a collection of universal class loaders for PHP 5.3+ compatible with
[PSR-0](http://groups.google.com/group/php-standards/web/psr-0-final-proposal) class
naming standard. They can be used for loading any project that follows these
naming rules.

Version information
-------------------

This is a development version of Open Power Autoloader 3.0.3.0

Requirements
------------

+ PHP 5.3 or 5.4
+ [Symfony 2 Console Component](http://www.symfony-reloaded.org) (Optional, recommended)
+ [Advanced PHP Cache](http://pecl.php.net/package/APC) (Optional)
+ [chdb](http://pecl.php.net/package/chdb) (Optional)

Contents
--------

The package provides the following class loaders:

* `\Opl\Autoloader\GenericLoader` - generic class loader with dynamic class-to-file
  translation.
* `\Opl\Autoloader\UniversalLoader` - a slower variant of `GenericLoader` that allows
  to register subnamespaces, too.
* `\Opl\Autoloader\ClassMapLoader` - class loader which uses a predefined map of
  classes and their paths. Provides greater performance at the cost of flexibility.
* `\Opl\Autoloader\PHARLoader` - class loader with predefined maps of classes for
  self-contained PHAR archives with web and console applications.
* `\Opl\Autoloader\ApcLoader` - a modification of `ClassMapLoader` which allows to
  cache the class maps in the [APC](http://pecl.php.net/package/APC) shared memory.
* `\Opl\Autoloader\ChdbLoader` - a modification of `ClassMapLoader` which uses Unix
  shared memory files and [chdb](http://pecl.php.net/package/chdb) caching extension
  to store the class maps.

Extra classes:

* `\Opl\Autoloader\CoreTracker` - an autoloader decorator that allows to find the common application
  core loaded every time.

Extra tools:

* *Class map builder* - produces the class maps for the given namespaces in the serialized
  array or chdb shared memory file formats. 
* *CoreDump* - exports the core dump generated by the `CoreTracker`, concatenating it 
  into a single PHP file or a list of *require* statements.

Documentation can be found [here](http://static.invenzzia.org/docs/opl/3_0/book/en/autoloader.html).

License and authors
-------------------

Open Power Autoloader is a part of Open Power Libs 3 foundation - Copyright (c) Invenzzia
Group 2008-2011. It is distributed under the terms of [New BSD License](http://www.invenzzia.org/license/new-bsd).

Authors:

+ Tomasz Jędrzejewski - idea, programming.