Open Power Autoloader 3.0.2.0
=============================

This is a collection of universal class loaders for PHP 5.3+ compatible with
[PSR-0](http://groups.google.com/group/php-standards/web/psr-0-final-proposal) class
naming standard. They can be used for loading any project that follows these
naming rules.

Version information
-------------------

This is a development version of Open Power Autoloader 3.0.2.0

Requirements
------------

+ PHP 5.3+
+ [Open Power Cache](http://www.github.com/OPL/opl3-cache) (Optional)
+ [Symfony 2 Console Component](http://www.symfony-reloaded.org) (Optional)

Contents
--------

The package provides the following class loaders:

* `\Opl\Autoloader\GenericLoader` - generic class loader with dynamic class-to-file
  translation.
* `\Opl\Autoloader\ClassMapLoader` - class loader which uses a predefined map of
  classes and their paths. Provides greater performance at the cost of flexibility.
* `\Opl\Autoloader\PHARLoader` - class loader with predefined maps of classes for
  self-contained PHAR archives with web and console applications.

Extra classes:

* `\Opl\Autoloader\ClassMapBuilder` - class map builder for the map-based autoloaders.
* `\Opl\Autoloader\CoreTracker` - an autoloader decorator that allows to find the common application
  core loaded every time.
* `\Opl\Autoloader\Command\ClassMapBuild` - Symfony 2 Console command that builds
  the class maps for the map-based autoloaders.
* `\Opl\Autoloader\Command\CoreDump` - Symfony 2 Console command that generates the
  application core loading code from the `CoreTracker` dump.

Documentation can be found [here](http://static.invenzzia.org/docs/opl/3_0/book/en/autoloader.html).

License and authors
-------------------

Open Power Autoloader is a part of Open Power Libs 3 foundation - Copyright (c) Invenzzia
Group 2008-2011. It is distributed under the terms of [New BSD License](http://www.invenzzia.org/license/new-bsd).

Authors:

+ Tomasz Jędrzejewski - idea, programming.