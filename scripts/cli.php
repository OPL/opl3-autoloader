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
require_once(__DIR__.'/../autoload.php');
$cli = new \Symfony\Component\Console\Application('Open Power Autoloader Command Line Interface', '3.0');
$cli->setCatchExceptions(true);

$cli->addCommands(array(
	new \Opl\Autoloader\Command\ClassMapBuild(),
));
$cli->run();