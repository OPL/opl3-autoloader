<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */

define('DIR_LIB', '../lib/');

// Configure your autoloader here, by commenting out the
// autoloaders you do not use and fixing the paths to match
// your filesystem


/**
 * GenericAutoloader (recommended)
 */
require(DIR_LIB.'Opl/Autoloader/GenericLoader.php');
use Opl\Autoloader\GenericLoader;
$loader = new GenericLoader(DIR_LIB);
// do not add "Opl" and "Symfony" at the end of the paths!
$loader->addNamespace('TestSuite', './');
$loader->addNamespace('Opl', '/your/path/here');
$loader->addNamespace('Symfony', '/your/path/here');
$loader->register();

/**
 * ClassMapAutoloader - you must generate a valid class map
 * before using it.
 */
require(DIR_LIB.'Opl/Autoloader/ClassMapLoader.php');
use Opl\Autoloader\ClassMapLoader;
$loader = new ClassMapLoader('/path/to/the/classmap', DIR_LIB);
// do not add "Opl" and "Symfony" at the end of the paths!
$loader->addNamespace('TestSuite', './');
$loader->addNamespace('Opl', '/your/path/here');
$loader->addNamespace('Symfony', '/your/path/here');
$loader->register();