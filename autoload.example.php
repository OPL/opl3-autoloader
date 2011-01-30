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
// Copy this file to autoload.php in order to get the tests work.

require_once(__DIR__.'/src/Opl/Autoloader/GenericLoader.php');
use Opl\Autoloader\GenericLoader;

$loader = new GenericLoader('./src/');

// Please provide here the correct path to the Symfony as
// a secondary argument, if different than the default one.
$loader->addLibrary('Opl');
// Please provide here the correct path to the Symfony as
// a secondary argument, if different than the default one.
$loader->addLibrary('Symfony');
$loader->register();
