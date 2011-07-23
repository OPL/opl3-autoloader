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
namespace Opl\Autoloader;

/**
 * This autoloader can be used for standalone PHAR archives. The class map
 * can be defined as a part of the stub.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class PHARLoader
{
	/**
	 * The loaded class map.
	 * @var array
	 * @internal
	 */
	protected $classMap;
	/**
	 * The path to the PHAR file (for testing purposes only.
	 * @var string
	 */
	protected $path = __FILE__;

	/**
	 * Creates the class map loader and loads the map into the memory.
	 * The map must be constructed with the command line interface.
	 *
	 * @param array $classMap The valid class map
	 * @param string $defaultPath The default location path used for newly registered libraries
	 */
	public function __construct(array $classMap)
	{
		$this->classMap = $classMap;
	} // end __construct();

	/**
	 * Installs this class loader on the SPL autoload stack.
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	} // end register();

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));
	} // end unregister();

	/**
	 * Attempts to load the specified class from a file.
	 *
	 * @param string $className The class name.
	 * @return boolean
	 */
	public function loadClass($className)
	{
		if(!isset($this->classMap[$className]))
		{
			return false;
		}
		require('phar://'.$this->path.'/'.$this->classMap[$className][1]);
		return true;
	} // end loadClass();
} // end PHARLoader;
